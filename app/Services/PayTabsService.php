<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayTabsService
{
    public function __construct(private OrderFulfillmentService $fulfillment)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->profileId()) && filled($this->serverKey());
    }

    public function isEnabledByAdmin(): bool
    {
        return (bool) SettingService::get('paytabs_enabled', true);
    }

    protected function profileId(): ?string
    {
        return config('edvora.paytabs.profile_id') ?: SettingService::get('paytabs_profile_id');
    }

    protected function serverKey(): ?string
    {
        return config('edvora.paytabs.server_key') ?: SettingService::get('paytabs_server_key');
    }

    protected function region(): string
    {
        return config('edvora.paytabs.region') ?: (SettingService::get('paytabs_region') ?: 'egypt');
    }

    protected function currency(): string
    {
        return SettingService::currency();
    }

    protected function baseUrl(): string
    {
        return match ($this->region()) {
            'ksa' => 'https://secure.paytabs.sa',
            'uae' => 'https://secure.paytabs.com',
            'oman' => 'https://secure-oman.paytabs.com',
            'jordan' => 'https://secure-jordan.paytabs.com',
            'kuwait' => 'https://secure-kuwait.paytabs.com',
            'iraq' => 'https://secure-iraq.paytabs.com',
            'morocco' => 'https://secure-morocco.paytabs.com',
            'qatar' => 'https://secure-doha.paytabs.com',
            'global' => 'https://secure-global.paytabs.com',
            default => 'https://secure-egypt.paytabs.com',
        };
    }

    public function createPaymentPage(Order $order): array
    {
        if (! $this->isConfigured()) {
            if (! config('edvora.payments.allow_demo')) {
                throw new RuntimeException('PayTabs is not configured.');
            }

            return [
                'demo' => true,
                'redirect_url' => route('checkout.paytabs.demo', $order),
            ];
        }

        $order->loadMissing('items.course.translations', 'user');

        [$firstName, $lastName] = $this->splitName($order->user?->name ?: 'Student');

        $response = Http::withHeaders(['Authorization' => $this->serverKey()])
            ->timeout(30)
            ->post("{$this->baseUrl()}/payment/request", [
                'profile_id' => (int) $this->profileId(),
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => $order->number,
                'cart_currency' => $this->currency(),
                'cart_amount' => (float) $order->total,
                'cart_description' => "Edvora order {$order->number}",
                'customer_details' => [
                    'name' => trim("{$firstName} {$lastName}"),
                    'email' => $order->user?->email ?: 'student@edvora.test',
                    'phone' => $this->normalizePhone($order->user?->phone),
                    'street1' => 'NA',
                    'city' => 'Cairo',
                    'state' => 'NA',
                    'country' => 'EG',
                    'zip' => 'NA',
                ],
                'return' => route('checkout.paytabs.return'),
                'callback' => route('webhooks.paytabs'),
            ])->throw()->json();

        $this->fulfillment->markPendingPayment($order, 'paytabs', (string) ($response['tran_ref'] ?? ''), $response);

        return [
            'demo' => false,
            'redirect_url' => $response['redirect_url'],
            'tran_ref' => $response['tran_ref'] ?? null,
        ];
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $key = $this->serverKey();

        if (! $key) {
            return app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks');
        }

        if (! $signature) {
            return false;
        }

        $calculated = hash_hmac('sha256', $rawBody, $key);

        return hash_equals(strtolower($calculated), strtolower($signature));
    }

    public function verifyReturnSignature(array $payload, ?string $signature): bool
    {
        $key = $this->serverKey();

        if (! $key) {
            return app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks');
        }

        if (! $signature) {
            return false;
        }

        $calculated = hash_hmac('sha256', json_encode($payload), $key);

        return hash_equals(strtolower($calculated), strtolower($signature));
    }

    public function handleWebhook(string $rawBody, ?string $signature): void
    {
        if (! $this->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('PayTabs webhook signature verification failed', ['body' => $rawBody]);

            throw new RuntimeException('Invalid PayTabs signature.');
        }

        $payload = json_decode($rawBody, true) ?? [];
        $this->applyResult($payload);
    }

    public function handleReturn(array $payload, ?string $signature): bool
    {
        if (! $this->verifyReturnSignature($payload, $signature)) {
            Log::info('PayTabs return signature unavailable or invalid, relying on async callback', [
                'cart_id' => data_get($payload, 'cart_id'),
            ]);

            return $this->isSuccessfulResult($payload);
        }

        return $this->applyResult($payload);
    }

    public function confirmPaymentForOrder(Order $order, ?string $tranRef = null): bool
    {
        if ($order->status === 'paid') {
            return true;
        }

        if (! $this->isConfigured()) {
            return false;
        }

        $order->loadMissing('payment');

        $reference = $tranRef ?: $order->payment?->provider_reference;

        if (! $reference) {
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->serverKey()])
                ->timeout(30)
                ->post("{$this->baseUrl()}/payment/query", [
                    'profile_id' => (int) $this->profileId(),
                    'tran_ref' => $reference,
                ])->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('PayTabs transaction query failed', [
                'order_id' => $order->id,
                'tran_ref' => $reference,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return $this->applyResult(is_array($response) ? $response : []);
    }

    protected function applyResult(array $payload): bool
    {
        $order = Order::query()
            ->where('number', data_get($payload, 'cart_id'))
            ->with(['items.course', 'user', 'coupon'])
            ->first();

        if (! $order) {
            Log::warning('PayTabs result order not found', ['cart_id' => data_get($payload, 'cart_id')]);

            return false;
        }

        $reference = (string) data_get($payload, 'tran_ref', '');

        if (! $this->isSuccessfulResult($payload)) {
            $this->fulfillment->markFailed($order, 'paytabs', $reference, $payload);

            return false;
        }

        $amountMinorUnits = (int) round(((float) data_get($payload, 'cart_amount', 0)) * 100);

        if (! $this->fulfillment->amountsMatch($order, $amountMinorUnits, data_get($payload, 'cart_currency'), 'paytabs')) {
            return false;
        }

        $this->fulfillment->markPaid($order, 'paytabs', $reference, $payload);

        return true;
    }

    protected function isSuccessfulResult(array $payload): bool
    {
        return data_get($payload, 'payment_result.response_status') === 'A'
            || data_get($payload, 'respStatus') === 'A';
    }

    protected function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];
        $first = $parts[0] ?? 'Student';
        $last = $parts[1] ?? 'Edvora';

        return [$first, $last];
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (! $digits || strlen($digits) < 8) {
            return '01000000000';
        }

        return $digits;
    }
}
