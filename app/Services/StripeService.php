<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeService
{
    public function __construct(private OrderFulfillmentService $fulfillment)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->secret());
    }

    public function publishableKey(): ?string
    {
        return config('edvora.stripe.key') ?: SettingService::get('stripe_key');
    }

    protected function secret(): ?string
    {
        return config('edvora.stripe.secret') ?: SettingService::get('stripe_secret');
    }

    protected function webhookSecret(): ?string
    {
        return config('edvora.stripe.webhook_secret') ?: SettingService::get('stripe_webhook_secret');
    }

    protected function boot(): void
    {
        $secret = $this->secret();

        if (! $secret) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        Stripe::setApiKey($secret);
    }

    public function createCheckoutSession(Order $order): Session
    {
        $this->boot();

        $order->loadMissing('items.course.translations', 'user');

        $lineItems = $order->items->map(function ($item) use ($order) {
            $title = $item->course->translation()?->title ?? 'Course';

            return [
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'product_data' => [
                        'name' => $title,
                        'metadata' => [
                            'course_id' => (string) $item->course_id,
                        ],
                    ],
                    'unit_amount' => (int) round(((float) $item->price) * 100),
                ],
                'quantity' => 1,
            ];
        })->values()->all();

        $session = Session::create([
            'mode' => 'payment',
            'customer_email' => $order->user?->email,
            'client_reference_id' => (string) $order->id,
            'success_url' => route('checkout.success', $order).'?provider=stripe&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', $order),
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->number,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'order_number' => $order->number,
                ],
            ],
            'line_items' => $lineItems,
        ]);

        $this->fulfillment->markPendingPayment($order, 'stripe', $session->id, [
            'session_id' => $session->id,
            'url' => $session->url,
        ]);

        return $session;
    }

    public function confirmSession(Order $order, string $sessionId): bool
    {
        $this->boot();

        $session = Session::retrieve($sessionId);

        $orderId = (string) ($session->metadata->order_id ?? $session->client_reference_id ?? '');
        if ($orderId !== (string) $order->id) {
            Log::warning('Stripe session order mismatch', [
                'order_id' => $order->id,
                'session_order_id' => $orderId,
                'session_id' => $sessionId,
            ]);

            return false;
        }

        if (($session->payment_status ?? null) !== 'paid' && ($session->status ?? null) !== 'complete') {
            return false;
        }

        $amountTotal = $session->amount_total ?? null;
        $currency = $session->currency ? strtoupper($session->currency) : null;

        if (! $this->fulfillment->amountsMatch($order, $amountTotal, $currency, 'stripe')) {
            return false;
        }

        $this->fulfillment->markPaid(
            $order->load('items.course', 'user', 'coupon'),
            'stripe',
            $session->payment_intent ?: $session->id,
            $session->toArray()
        );

        return true;
    }

    public function handleWebhook(string $payload, ?string $signature): void
    {
        $secret = $this->webhookSecret();

        if ($secret) {
            if (! $signature) {
                throw new UnexpectedValueException('Missing Stripe signature.');
            }

            try {
                $event = Webhook::constructEvent($payload, $signature, $secret);
            } catch (SignatureVerificationException $e) {
                throw $e;
            }
        } elseif (app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks')) {
            $event = json_decode($payload);
            if (! $event) {
                throw new UnexpectedValueException('Invalid Stripe payload.');
            }
        } else {
            throw new UnexpectedValueException('Stripe webhook secret is not configured.');
        }

        $type = $event->type ?? null;
        $object = $event->data->object ?? null;

        if (! $type || ! $object) {
            return;
        }

        if ($type === 'checkout.session.completed' || $type === 'checkout.session.async_payment_succeeded') {
            $orderId = $object->metadata->order_id ?? $object->client_reference_id ?? null;
            $order = $orderId ? Order::query()->with(['items.course', 'user', 'coupon'])->find($orderId) : null;

            if (! $order) {
                return;
            }

            if (! $this->fulfillment->amountsMatch($order, $object->amount_total ?? null, isset($object->currency) ? strtoupper($object->currency) : null, 'stripe')) {
                return;
            }

            if (($object->payment_status ?? null) === 'paid' || ($object->status ?? null) === 'complete') {
                $this->fulfillment->markPaid(
                    $order,
                    'stripe',
                    $object->payment_intent ?? $object->id,
                    json_decode(json_encode($object), true) ?: []
                );
            }

            return;
        }

        if (in_array($type, ['checkout.session.expired', 'checkout.session.async_payment_failed', 'payment_intent.payment_failed'], true)) {
            $orderId = $object->metadata->order_id ?? $object->client_reference_id ?? null;
            $order = $orderId ? Order::query()->find($orderId) : null;

            if ($order) {
                $this->fulfillment->markFailed(
                    $order,
                    'stripe',
                    $object->id ?? null,
                    json_decode(json_encode($object), true) ?: []
                );
            }
        }
    }
}
