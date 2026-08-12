<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\InstructorEarning;
use App\Models\Order;
use App\Notifications\OrderPaidNotification;
use App\Repositories\CartRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderFulfillmentService
{
    public function __construct(
        private PaymentRepository $payments,
        private CartRepository $cart,
    ) {
    }

    public function markPaid(Order $order, string $provider, ?string $reference = null, array $payload = []): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $provider, $reference, $payload) {
            $order->update([
                'status' => 'paid',
                'payment_method' => $provider,
            ]);

            $this->payments->upsertForOrder($order->id, [
                'provider' => $provider,
                'provider_reference' => $reference,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => 'paid',
                'payload' => $payload,
            ]);

            if ($order->coupon) {
                $order->coupon->increment('used_count');
            }

            foreach ($order->items as $item) {
                Enrollment::query()->firstOrCreate(
                    [
                        'user_id' => $order->user_id,
                        'course_id' => $item->course_id,
                    ],
                    [
                        'order_id' => $order->id,
                        'enrolled_at' => now(),
                    ]
                );

                $item->course->increment('students_count');

                InstructorEarning::query()->firstOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'instructor_id' => $item->instructor_id,
                        'course_id' => $item->course_id,
                        'amount' => $item->instructor_earning,
                        'status' => 'available',
                    ]
                );
            }

            $this->cart->clearForUser($order->user_id);

            ActivityLog::record('order.paid', $order, ['provider' => $provider, 'reference' => $reference]);

            $order->user?->notify(new OrderPaidNotification($order));
        });
    }

    public function markFailed(Order $order, string $provider, ?string $reference = null, array $payload = []): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $provider, $reference, $payload) {
            $order->update([
                'status' => 'failed',
                'payment_method' => $provider,
            ]);

            $this->payments->upsertForOrder($order->id, [
                'provider' => $provider,
                'provider_reference' => $reference,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => 'failed',
                'payload' => $payload,
            ]);

            ActivityLog::record('order.failed', $order, ['provider' => $provider, 'reference' => $reference]);
        });
    }

    public function markPendingPayment(Order $order, string $provider, ?string $reference = null, array $payload = []): void
    {
        $order->update(['payment_method' => $provider]);

        $this->payments->upsertForOrder($order->id, [
            'provider' => $provider,
            'provider_reference' => $reference,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => 'pending',
            'payload' => $payload,
        ]);
    }

    public function amountsMatch(Order $order, int|float|string|null $paidMinorUnits, ?string $currency = null, ?string $provider = null): bool
    {
        if ($paidMinorUnits === null) {
            return false;
        }

        $expected = (int) round(((float) $order->total) * 100);
        $paid = (int) $paidMinorUnits;

        if ($expected !== $paid) {
            Log::warning('Payment amount mismatch', [
                'order_id' => $order->id,
                'expected' => $expected,
                'paid' => $paid,
            ]);

            return false;
        }

        if (! $currency) {
            return true;
        }

        $orderCurrency = strtoupper((string) $order->currency);
        $paidCurrency = strtoupper($currency);
        $provider = $provider ?: $order->payment_method;

        if ($paidCurrency === $orderCurrency) {
            return true;
        }

        $paymobCurrency = strtoupper((string) (config('edvora.paymob.currency') ?: 'EGP'));
        if ($provider === 'paymob' && $paidCurrency === $paymobCurrency) {
            return true;
        }

        Log::warning('Payment currency mismatch', [
            'order_id' => $order->id,
            'expected' => $order->currency,
            'paid' => $currency,
            'provider' => $provider,
        ]);

        return false;
    }
}
