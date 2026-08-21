<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private CartRepository $cart,
        private CommissionService $commission,
        private StripeService $stripe,
        private PaymobService $paymob,
        private PayTabsService $paytabs,
        private PayPalService $paypal,
        private FawryService $fawry,
        private OrderFulfillmentService $fulfillment,
    ) {
    }

    public function summary(User $user, ?string $couponCode = null): array
    {
        $items = $this->cart->itemsForUser($user->id);
        $subtotal = $items->sum(fn($item) => (float) $item->course->price);
        $coupon = $couponCode ? $this->cart->findCouponByCode($couponCode) : null;
        $discount = $coupon?->discountFor($subtotal) ?? 0;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max($subtotal - $discount, 0),
            'coupon' => $coupon,
            'currency' => SettingService::currency(),
            'providers' => [
                'stripe' => [
                    'enabled' => $this->stripe->isEnabledByAdmin() && ($this->stripe->isConfigured() || config('edvora.payments.allow_demo')),
                    'configured' => $this->stripe->isConfigured(),
                    'publishable_key' => $this->stripe->publishableKey(),
                ],
                'paymob' => [
                    'enabled' => $this->paymob->isEnabledByAdmin() && ($this->paymob->isConfigured() || config('edvora.payments.allow_demo')),
                    'configured' => $this->paymob->isConfigured(),
                ],
                'paytabs' => [
                    'enabled' => $this->paytabs->isEnabledByAdmin() && ($this->paytabs->isConfigured() || config('edvora.payments.allow_demo')),
                    'configured' => $this->paytabs->isConfigured(),
                ],
                'paypal' => [
                    'enabled' => $this->paypal->isEnabledByAdmin() && ($this->paypal->isConfigured() || config('edvora.payments.allow_demo')),
                    'configured' => $this->paypal->isConfigured(),
                ],
                'fawry' => [
                    'enabled' => $this->fawry->isEnabledByAdmin() && ($this->fawry->isConfigured() || config('edvora.payments.allow_demo')),
                    'configured' => $this->fawry->isConfigured(),
                ],
            ],
            'demo_mode' => (bool) config('edvora.payments.allow_demo'),
        ];
    }

    public function applyCoupon(User $user, string $code): array
    {
        $coupon = $this->cart->findCouponByCode($code);
        $subtotal = $this->cart->itemsForUser($user->id)
            ->sum(fn($item) => (float) $item->course->price);

        if (!$coupon || !$coupon->isValid($subtotal)) {
            return ['ok' => false, 'message' => __('Invalid coupon.')];
        }

        return ['ok' => true, 'code' => $coupon->code, 'message' => __('Coupon applied.')];
    }

    public function startPayment(User $user, string $provider, ?string $couponCode = null): array
    {
        $cartItems = $this->cart->itemsForUser($user->id);
        if ($cartItems->isEmpty()) {
            return ['ok' => false, 'message' => __('Cart is empty.'), 'redirect' => route('cart.index')];
        }

        if (!in_array($provider, ['stripe', 'paymob', 'paytabs', 'paypal', 'fawry'], true)) {
            return ['ok' => false, 'message' => __('Invalid payment method.'), 'redirect' => route('checkout.show')];
        }

        $order = DB::transaction(function () use ($cartItems, $user, $couponCode, $provider) {
            $subtotal = $cartItems->sum(fn($item) => (float) $item->course->price);
            $coupon = $couponCode ? $this->cart->findCouponByCode($couponCode) : null;

            if ($coupon && !$coupon->isValid($subtotal)) {
                $coupon = null;
            }

            $discount = $coupon?->discountFor($subtotal) ?? 0;
            $currency = SettingService::currency();

            $order = Order::query()->create([
                'number' => 'EDV-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'coupon_id' => $coupon?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max($subtotal - $discount, 0),
                'currency' => $currency,
                'status' => 'pending',
                'payment_method' => $provider,
            ]);

            $ratio = $subtotal > 0 ? ($order->total / $subtotal) : 1;

            foreach ($cartItems as $cartItem) {
                $price = round((float) $cartItem->course->price * $ratio, 2);
                $split = $this->commission->split($cartItem->course, $price);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'course_id' => $cartItem->course_id,
                    'instructor_id' => $cartItem->course->instructor_id,
                    'price' => $price,
                    'commission_rate' => $split['commission_rate'],
                    'platform_earning' => $split['platform_earning'],
                    'instructor_earning' => $split['instructor_earning'],
                ]);
            }

            return $order->load('items.course.translations', 'user', 'coupon');
        });

        // Free / 100% discounted orders skip gateways.
        if ((float) $order->total <= 0) {
            $this->fulfillment->markPaid($order, $provider, 'free-' . Str::lower(Str::random(8)), [
                'type' => 'free_order',
            ]);

            return [
                'ok' => true,
                'redirect' => route('checkout.success', ['order' => $order, 'provider' => $provider]),
            ];
        }

        return match ($provider) {
            'stripe' => $this->startStripe($order),
            'paytabs' => $this->startPayTabs($order),
            'paypal' => $this->startPayPal($order),
            'fawry' => $this->startFawry($order),
            default => $this->startPaymob($order),
        };
    }

    protected function startStripe(Order $order): array
    {
        if (!$this->stripe->isConfigured()) {
            if (!config('edvora.payments.allow_demo')) {
                return [
                    'ok' => false,
                    'message' => __('Stripe is not configured.'),
                    'redirect' => route('checkout.show'),
                ];
            }

            $this->fulfillment->markPendingPayment($order, 'stripe', 'demo-pending');

            return [
                'ok' => true,
                'redirect' => route('checkout.success', ['order' => $order, 'provider' => 'stripe', 'demo' => 1]),
            ];
        }

        try {
            $session = $this->stripe->createCheckoutSession($order);

            return ['ok' => true, 'redirect' => $session->url];
        } catch (\Throwable $e) {
            Log::error('Stripe checkout failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $this->fulfillment->markFailed($order, 'stripe', null, [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Unable to start Stripe payment. Please try again.'),
                'redirect' => route('checkout.show'),
            ];
        }
    }

    protected function startPaymob(Order $order): array
    {
        try {
            $paymobSession = $this->paymob->createPaymentKey($order);

            return ['ok' => true, 'redirect' => $paymobSession['iframe_url']];
        } catch (\Throwable $e) {
            Log::error('Paymob checkout failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $this->fulfillment->markFailed($order, 'paymob', null, [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Unable to start Paymob payment. Please try again.'),
                'redirect' => route('checkout.show'),
            ];
        }
    }

    protected function startPayTabs(Order $order): array
    {
        try {
            $paytabsSession = $this->paytabs->createPaymentPage($order);

            return ['ok' => true, 'redirect' => $paytabsSession['redirect_url']];
        } catch (\Throwable $e) {
            Log::error('PayTabs checkout failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $this->fulfillment->markFailed($order, 'paytabs', null, [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Unable to start PayTabs payment. Please try again.'),
                'redirect' => route('checkout.show'),
            ];
        }
    }

    protected function startPayPal(Order $order): array
    {
        try {
            $paypalSession = $this->paypal->createOrder($order);

            return ['ok' => true, 'redirect' => $paypalSession['approve_url']];
        } catch (\Throwable $e) {
            Log::error('PayPal checkout failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $this->fulfillment->markFailed($order, 'paypal', null, [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Unable to start PayPal payment. Please try again.'),
                'redirect' => route('checkout.show'),
            ];
        }
    }

    protected function startFawry(Order $order): array
    {
        if (!$this->fawry->isConfigured()) {
            if (!config('edvora.payments.allow_demo')) {
                return [
                    'ok' => false,
                    'message' => __('Fawry is not configured.'),
                    'redirect' => route('checkout.show'),
                ];
            }

            $this->fulfillment->markPendingPayment($order, 'fawry', 'demo-pending');

            return [
                'ok' => true,
                'redirect' => route('checkout.fawry.demo', $order),
            ];
        }

        try {
            $fawrySession = $this->fawry->createPaymentRequest($order);

            return ['ok' => true, 'redirect' => $fawrySession['redirect_url']];
        } catch (\Throwable $e) {
            Log::error('Fawry checkout failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $this->fulfillment->markFailed($order, 'fawry', null, [
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => __('Unable to start Fawry payment. Please try again.'),
                'redirect' => route('checkout.show'),
            ];
        }
    }


    public function completeSuccess(
        User $user,
        Order $order,
        bool $demo = false,
        ?string $provider = null,
        ?string $sessionId = null,
        ?string $paymobTransactionId = null,
        ?string $paytabsTranRef = null,
    ): Order {
        abort_unless($order->user_id === $user->id, 403);

        $order->loadMissing('items.course.translations', 'user', 'coupon', 'payment');

        if ($order->status !== 'paid') {
            if ($provider === 'stripe' && $sessionId && $this->stripe->isConfigured()) {
                try {
                    $this->stripe->confirmSession($order, $sessionId);
                } catch (\Throwable $e) {
                    Log::warning('Stripe session confirmation failed', [
                        'order_id' => $order->id,
                        'session_id' => $sessionId,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            if ($provider === 'paymob' && $this->paymob->isConfigured()) {
                $this->paymob->confirmPaymentForOrder($order, $paymobTransactionId);
            }

            if ($provider === 'paytabs' && $this->paytabs->isConfigured()) {
                $this->paytabs->confirmPaymentForOrder($order, $paytabsTranRef);
            }

            $order->refresh();

            if ($order->status !== 'paid' && $demo && config('edvora.payments.allow_demo')) {
                $this->fulfillment->markPaid(
                    $order->load('items.course', 'user', 'coupon'),
                    $provider ?: ($order->payment_method ?: 'stripe'),
                    'demo-' . Str::random(8),
                    ['demo' => true]
                );
            }
        }

        $this->cart->clearForUser($user->id);

        return $order->fresh(['items.course.translations', 'payment']);
    }
}
