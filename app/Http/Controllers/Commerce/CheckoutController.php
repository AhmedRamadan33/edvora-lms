<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\ApplyCouponRequest;
use App\Http\Requests\Commerce\PayOrderRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\OrderFulfillmentService;
use App\Services\PaymobService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, CheckoutService $checkout): View|RedirectResponse
    {
        $summary = $checkout->summary(auth()->user(), $request->session()->get('coupon_code'));

        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Cart is empty.'));
        }

        return view('commerce.checkout', $summary);
    }

    public function applyCoupon(ApplyCouponRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $result = $checkout->applyCoupon(auth()->user(), $request->validated('code'));

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        $request->session()->put('coupon_code', $result['code']);

        return back()->with('success', $result['message']);
    }

    public function pay(PayOrderRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $result = $checkout->startPayment(
            auth()->user(),
            $request->validated('provider'),
            $request->session()->get('coupon_code')
        );

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->to($result['redirect'] ?? route('checkout.show'))
                ->with('error', $result['message'] ?? __('Unable to start payment.'));
        }

        $url = $result['redirect'];

        return str_starts_with($url, 'http')
            ? redirect()->away($url)
            : redirect()->to($url);
    }

    public function success(Request $request, Order $order, CheckoutService $checkout): View|RedirectResponse
    {
        $order = $checkout->completeSuccess(
            auth()->user(),
            $order,
            $request->boolean('demo'),
            $request->get('provider'),
            $request->get('session_id'),
            $request->get('id'),
        );

        $request->session()->forget('coupon_code');

        if ($order->status !== 'paid') {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Payment is still pending. If you were charged, your enrollment will unlock shortly.'));
        }

        return view('commerce.success', ['order' => $order]);
    }

    public function cancel(Order $order, OrderFulfillmentService $fulfillment): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        if ($order->status === 'pending') {
            $fulfillment->markFailed($order, $order->payment_method ?: 'stripe', null, [
                'cancelled_by_user' => true,
            ]);
        }

        return redirect()->route('cart.index')->with('error', __('Payment cancelled.'));
    }

    public function paymobReturn(Request $request, PaymobService $paymob): RedirectResponse
    {
        $merchantOrderId = (string) $request->query('merchant_order_id', '');
        $order = null;

        if ($merchantOrderId !== '') {
            $order = Order::query()->where('number', $merchantOrderId)->first();
        }

        if (! $order && $request->filled('order')) {
            $order = Order::query()
                ->whereHas('payment', fn ($q) => $q->where('provider', 'paymob')->where('provider_reference', $request->query('order')))
                ->first();
        }

        if (! $order) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Paymob payment could not be matched to an order.'));
        }

        if (auth()->check() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $ok = $paymob->handleReturn($order, $request->query());

        if (! $ok) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Paymob payment failed or could not be verified.'));
        }

        if (! auth()->check()) {
            return redirect()->route('login')->with('success', __('Payment successful. Please sign in to access your courses.'));
        }

        return redirect()->route('checkout.success', [
            'order' => $order,
            'provider' => 'paymob',
            'id' => $request->query('id'),
        ]);
    }

    public function paymobDemo(Order $order, OrderFulfillmentService $fulfillment): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless(config('edvora.payments.allow_demo'), 404);

        $fulfillment->markPaid(
            $order->load('items.course', 'user', 'coupon'),
            'paymob',
            'demo-'.Str::random(8),
            ['demo' => true]
        );

        return redirect()->route('checkout.success', [
            'order' => $order,
            'provider' => 'paymob',
            'demo' => 1,
        ]);
    }
}
