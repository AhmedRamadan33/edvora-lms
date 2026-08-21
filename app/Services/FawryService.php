<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FawryService
{
    public function __construct(private OrderFulfillmentService $fulfillment)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->merchantCode()) && filled($this->securityKey());
    }

    public function isEnabledByAdmin(): bool
    {
        return (bool) SettingService::get('fawry_enabled', true);
    }

    protected function merchantCode(): ?string
    {
        return config('edvora.fawry.merchant_code') ?: SettingService::get('fawry_merchant_code');
    }

    protected function securityKey(): ?string
    {
        return config('edvora.fawry.security_key') ?: SettingService::get('fawry_security_key');
    }

    protected function mode(): string
    {
        return config('edvora.fawry.mode') ?: (SettingService::get('fawry_mode') ?: 'sandbox');
    }

    protected function baseUrl(): string
    {
        return $this->mode() === 'live'
            ? 'https://atfawry.com/fawrypay-api/api/payments/init'
            : 'https://atfawry.fawrystaging.com/fawrypay-api/api/payments/init';
    }

    /**
     * Build the SHA-256 signature for a Fawry payment request.
     * Signature = SHA256(merchantCode + merchantRefNum + customerMobile + customerEmail + amount + itemId + quantity + itemPrice + securityKey)
     */
    public function buildSignature(array $data): string
    {
        $plain = ($data['merchantCode'] ?? '')
            . ($data['merchantRefNum'] ?? '')
            . ($data['customerMobile'] ?? '')
            . ($data['customerEmail'] ?? '')
            . ($data['amount'] ?? '')
            . ($data['itemId'] ?? '')
            . ($data['quantity'] ?? '')
            . ($data['itemPrice'] ?? '')
            . $this->securityKey();

        return hash('sha256', $plain);
    }

    /**
     * Build the signature for a status-query request.
     * Signature = SHA256(merchantCode + merchantRefNum + securityKey)
     */
    protected function buildQuerySignature(string $merchantRefNum): string
    {
        return hash('sha256', $this->merchantCode() . $merchantRefNum . $this->securityKey());
    }

    /**
     * Build the webhook notification signature for verification.
     * Signature = SHA256(merchantCode + merchantRefNum + orderStatus + amount + securityKey)
     */
    protected function buildWebhookSignature(array $payload): string
    {
        return hash(
            'sha256',
            ($payload['merchantCode'] ?? '')
            . ($payload['merchantRefNum'] ?? '')
            . ($payload['orderStatus'] ?? '')
            . number_format((float) ($payload['paymentAmount'] ?? 0), 2, '.', '')
            . $this->securityKey()
        );
    }

    public function createPaymentRequest(Order $order): array
    {
        if (!$this->isConfigured()) {
            if (!config('edvora.payments.allow_demo')) {
                throw new RuntimeException('Fawry is not configured.');
            }

            return [
                'demo' => true,
                'redirect_url' => route('checkout.fawry.demo', $order),
            ];
        }

        $order->loadMissing('items.course.translations', 'user');

        $amount = number_format((float) $order->total, 2, '.', '');
        $itemId = $order->number;
        $qty = 1;
        $price = $amount;
        $phone = $this->normalizePhone($order->user?->phone);
        $email = $order->user?->email ?: 'student@edvora.test';
        $returnUrl = route('checkout.fawry.return');

        $signatureData = [
            'merchantCode' => $this->merchantCode(),
            'merchantRefNum' => $order->number,
            'customerMobile' => $phone,
            'customerEmail' => $email,
            'amount' => $amount,
            'itemId' => $itemId,
            'quantity' => (string) $qty,
            'itemPrice' => $price,
        ];

        $payload = [
            'merchantCode' => $this->merchantCode(),
            'merchantRefNum' => $order->number,
            'customerMobile' => $phone,
            'customerEmail' => $email,
            'paymentExpiry' => now()->addHours(48)->timestamp * 1000,
            'language' => 'ar-eg',
            'orderItems' => [
                [
                    'itemId' => $itemId,
                    'description' => "Edvora order {$order->number}",
                    'price' => (float) $price,
                    'quantity' => $qty,
                ],
            ],
            'returnUrl' => $returnUrl,
            'authCaptureModePayment' => false,
            'signature' => $this->buildSignature($signatureData),
        ];

        $response = Http::timeout(30)
            ->post($this->baseUrl(), $payload)
            ->throw()
            ->json();

        $redirectUrl = data_get($response, 'nextAction.redirectUrl')
            ?? data_get($response, 'redirectUrl')
            ?? null;

        if (!$redirectUrl) {
            throw new RuntimeException('Fawry did not return a redirect URL: ' . json_encode($response));
        }

        $this->fulfillment->markPendingPayment($order, 'fawry', $order->number, $response);

        return [
            'demo' => false,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Verify the webhook notification signature sent by Fawry.
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        $key = $this->securityKey();

        if (!$key) {
            return app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks');
        }

        $expected = $this->buildWebhookSignature($payload);
        $received = (string) ($payload['messageSignature'] ?? '');

        return $received !== '' && hash_equals($expected, $received);
    }

    public function handleWebhook(array $payload): void
    {
        if (!$this->verifyWebhookSignature($payload)) {
            Log::warning('Fawry webhook signature verification failed', ['payload' => $payload]);
            throw new RuntimeException('Invalid Fawry signature.');
        }

        $this->applyResult($payload);
    }

    public function handleReturn(Order $order, array $payload): bool
    {
        $orderStatus = strtoupper((string) ($payload['orderStatus'] ?? ''));

        if (!in_array($orderStatus, ['PAID', 'UNPAID'], true)) {
            // Ambiguous — try to confirm via API
            return $this->confirmPaymentForOrder($order);
        }

        if ($orderStatus !== 'PAID') {
            $this->fulfillment->markFailed($order, 'fawry', $order->number, $payload);
            return false;
        }

        return $this->applyResult(array_merge($payload, ['merchantRefNum' => $order->number]));
    }

    public function confirmPaymentForOrder(Order $order): bool
    {
        if ($order->status === 'paid') {
            return true;
        }

        if (!$this->isConfigured()) {
            return false;
        }

        $signature = $this->buildQuerySignature($order->number);

        $statusUrl = $this->mode() === 'live'
            ? 'https://atfawry.com/ECommerceWeb/Fawry/payments/status'
            : 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/status';

        try {
            $response = Http::timeout(30)
                ->get($statusUrl, [
                    'merchantCode' => $this->merchantCode(),
                    'merchantRefNum' => $order->number,
                    'signature' => $signature,
                ])
                ->throw()
                ->json();
        } catch (\Throwable $e) {
            Log::warning('Fawry status query failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return $this->applyResult(is_array($response) ? $response : []);
    }

    protected function applyResult(array $payload): bool
    {
        $refNum = (string) ($payload['merchantRefNum'] ?? '');

        $order = Order::query()
            ->where('number', $refNum)
            ->with(['items.course', 'user', 'coupon'])
            ->first();

        if (!$order) {
            Log::warning('Fawry result order not found', ['merchantRefNum' => $refNum]);
            return false;
        }

        $orderStatus = strtoupper((string) ($payload['orderStatus'] ?? ''));

        if ($orderStatus !== 'PAID') {
            $this->fulfillment->markFailed($order, 'fawry', $refNum, $payload);
            return false;
        }

        $amountMinorUnits = (int) round(((float) ($payload['paymentAmount'] ?? 0)) * 100);

        if (!$this->fulfillment->amountsMatch($order, $amountMinorUnits, 'EGP', 'fawry')) {
            return false;
        }

        $this->fulfillment->markPaid($order, 'fawry', $refNum, $payload);

        return true;
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (!$digits || strlen($digits) < 8) {
            return '01000000000';
        }

        return $digits;
    }
}
