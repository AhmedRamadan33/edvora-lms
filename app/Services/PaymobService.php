<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymobService
{
    protected string $baseUrl = 'https://accept.paymob.com';

    public function __construct(private OrderFulfillmentService $fulfillment)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey())
            && filled($this->integrationId())
            && filled($this->iframeId());
    }

    protected function apiKey(): ?string
    {
        return config('edvora.paymob.api_key') ?: SettingService::get('paymob_api_key');
    }

    protected function integrationId(): ?string
    {
        return config('edvora.paymob.integration_id') ?: SettingService::get('paymob_integration_id');
    }

    protected function iframeId(): ?string
    {
        return config('edvora.paymob.iframe_id') ?: SettingService::get('paymob_iframe_id');
    }

    protected function hmacSecret(): ?string
    {
        return config('edvora.paymob.hmac_secret') ?: SettingService::get('paymob_hmac_secret');
    }

    protected function currency(): string
    {
        return SettingService::currency();
    }

    protected function authToken(): string
    {
        return Http::timeout(30)->post("{$this->baseUrl}/api/auth/tokens", [
            'api_key' => $this->apiKey(),
        ])->throw()->json('token');
    }

    public function createPaymentKey(Order $order): array
    {
        if (! $this->isConfigured()) {
            if (! config('edvora.payments.allow_demo')) {
                throw new RuntimeException('Paymob is not configured.');
            }

            return [
                'demo' => true,
                'iframe_url' => route('checkout.paymob.demo', $order),
            ];
        }

        $order->loadMissing('items.course.translations', 'user');

        $auth = $this->authToken();
        $amountCents = (int) round(((float) $order->total) * 100);
        $currency = $this->currency();

        $items = $order->items->map(function ($item) {
            $name = $item->course->translation()?->title ?? 'Course';

            return [
                'name' => Str::limit($name, 45, ''),
                'amount_cents' => (int) round(((float) $item->price) * 100),
                'description' => Str::limit($name, 100, ''),
                'quantity' => 1,
            ];
        })->values()->all();

        $orderResponse = Http::timeout(30)->post("{$this->baseUrl}/api/ecommerce/orders", [
            'auth_token' => $auth,
            'delivery_needed' => false,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'merchant_order_id' => $order->number,
            'items' => $items,
        ])->throw()->json();

        [$firstName, $lastName] = $this->splitName($order->user?->name ?: 'Student');

        $paymentToken = Http::timeout(30)->post("{$this->baseUrl}/api/acceptance/payment_keys", [
            'auth_token' => $auth,
            'amount_cents' => $amountCents,
            'expiration' => 3600,
            'order_id' => $orderResponse['id'],
            'billing_data' => [
                'apartment' => 'NA',
                'email' => $order->user?->email ?: 'student@edvora.test',
                'floor' => 'NA',
                'first_name' => $firstName,
                'street' => 'NA',
                'building' => 'NA',
                'phone_number' => $this->normalizePhone($order->user?->phone),
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city' => 'Cairo',
                'country' => 'EG',
                'last_name' => $lastName,
                'state' => 'NA',
            ],
            'currency' => $currency,
            'integration_id' => (int) $this->integrationId(),
            'lock_order_when_paid' => true,
            'redirect_url' => route('checkout.paymob.return'),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->number,
            ],
        ])->throw()->json('token');

        $iframeUrl = sprintf(
            '%s/api/acceptance/iframes/%s?payment_token=%s',
            $this->baseUrl,
            $this->iframeId(),
            $paymentToken
        );

        $this->fulfillment->markPendingPayment($order, 'paymob', (string) $orderResponse['id'], [
            'paymob_order_id' => $orderResponse['id'],
            'iframe_url' => $iframeUrl,
            'currency' => $currency,
            'amount_cents' => $amountCents,
        ]);

        return [
            'demo' => false,
            'iframe_url' => $iframeUrl,
            'order_id' => $orderResponse['id'],
            'payment_token' => $paymentToken,
        ];
    }

    public function handleWebhook(array $payload, ?string $hmac = null): void
    {
        $obj = $this->normalizeTransactionPayload(data_get($payload, 'obj', $payload));

        if (! $this->verifyHmac($obj, $hmac ?: data_get($payload, 'hmac'))) {
            Log::warning('Paymob webhook HMAC verification failed', ['payload_keys' => array_keys($payload)]);
            throw new RuntimeException('Invalid Paymob HMAC.');
        }

        $this->processTransaction($obj, $payload);
    }

    public function handleReturn(Order $order, array $query): bool
    {
        $success = filter_var($query['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $reference = isset($query['id']) ? (string) $query['id'] : null;

        if (! $success) {
            if ($reference !== null || array_key_exists('pending', $query)) {
                $this->fulfillment->markFailed($order, 'paymob', $reference, $query);
            }

            return false;
        }

        if ($this->verifyHmac($query, $query['hmac'] ?? null)) {
            if ($this->fulfillment->amountsMatch(
                $order,
                $query['amount_cents'] ?? null,
                $query['currency'] ?? $this->currency(),
                'paymob'
            )) {
                $this->fulfillment->markPaid(
                    $order->load('items.course', 'user', 'coupon'),
                    'paymob',
                    $reference,
                    $query
                );

                return true;
            }
        } else {
            Log::info('Paymob return HMAC unavailable or invalid, confirming via inquiry API', [
                'order_id' => $order->id,
            ]);
        }

        return $this->confirmPaymentForOrder($order, $reference);
    }

    /**
     * Verify a pending order against Paymob's Transaction Inquiry API.
     */
    public function confirmPaymentForOrder(Order $order, ?string $transactionId = null): bool
    {
        if ($order->status === 'paid') {
            return true;
        }

        if (! $this->isConfigured()) {
            return false;
        }

        $order->loadMissing('payment');

        $transaction = null;

        if ($transactionId) {
            $transaction = $this->fetchTransactionById($transactionId);
        }

        if (! $transaction) {
            $transaction = $this->fetchTransactionForMerchantOrder($order->number);
        }

        if (! $transaction && $order->payment?->provider_reference) {
            $transaction = $this->fetchSuccessfulTransactionForPaymobOrder($order->payment->provider_reference);
        }

        if (! $transaction) {
            return false;
        }

        return $this->applySuccessfulTransaction($order, $transaction);
    }

    protected function processTransaction(array $obj, array $rawPayload): void
    {
        $success = filter_var(data_get($obj, 'success', false), FILTER_VALIDATE_BOOLEAN);
        $order = $this->resolveOrderFromTransaction($obj);

        if (! $order) {
            Log::warning('Paymob webhook order not found', [
                'merchant_order_id' => data_get($obj, 'order.merchant_order_id'),
                'paymob_order_id' => data_get($obj, 'order.id'),
            ]);

            return;
        }

        $reference = (string) (data_get($obj, 'id') ?? '');

        if (! $success) {
            $this->fulfillment->markFailed($order, 'paymob', $reference, $rawPayload);

            return;
        }

        $this->applySuccessfulTransaction($order, $obj);
    }

    protected function applySuccessfulTransaction(Order $order, array $transaction): bool
    {
        $success = filter_var(data_get($transaction, 'success', false), FILTER_VALIDATE_BOOLEAN);
        $pending = filter_var(data_get($transaction, 'pending', true), FILTER_VALIDATE_BOOLEAN);

        if (! $success || $pending) {
            return false;
        }

        $merchantOrderId = data_get($transaction, 'order.merchant_order_id');
        if ($merchantOrderId && (string) $merchantOrderId !== (string) $order->number) {
            Log::warning('Paymob transaction merchant order mismatch', [
                'order_id' => $order->id,
                'expected' => $order->number,
                'received' => $merchantOrderId,
            ]);

            return false;
        }

        if (! $this->fulfillment->amountsMatch(
            $order,
            data_get($transaction, 'amount_cents'),
            data_get($transaction, 'currency', $this->currency()),
            'paymob'
        )) {
            return false;
        }

        $this->fulfillment->markPaid(
            $order->load('items.course', 'user', 'coupon'),
            'paymob',
            (string) data_get($transaction, 'id', ''),
            $transaction
        );

        return true;
    }

    protected function resolveOrderFromTransaction(array $obj): ?Order
    {
        $merchantOrderId = data_get($obj, 'order.merchant_order_id')
            ?? data_get($obj, 'merchant_order_id');

        if ($merchantOrderId) {
            $order = Order::query()
                ->where('number', $merchantOrderId)
                ->with(['items.course', 'user', 'coupon'])
                ->first();

            if ($order) {
                return $order;
            }
        }

        $paymobOrderId = data_get($obj, 'order.id');
        if (! $paymobOrderId) {
            return null;
        }

        return Order::query()
            ->whereHas('payment', fn ($query) => $query
                ->where('provider', 'paymob')
                ->where('provider_reference', (string) $paymobOrderId))
            ->with(['items.course', 'user', 'coupon'])
            ->first();
    }

    protected function fetchTransactionById(string $transactionId): ?array
    {
        try {
            $response = Http::timeout(30)->get(
                "{$this->baseUrl}/api/acceptance/transactions/{$transactionId}",
                ['token' => $this->authToken()]
            )->throw()->json();

            return is_array($response) ? $response : null;
        } catch (\Throwable $e) {
            Log::warning('Paymob transaction inquiry failed', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function fetchTransactionForMerchantOrder(string $merchantOrderId): ?array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/api/ecommerce/orders/transaction_inquiry", [
                'auth_token' => $this->authToken(),
                'merchant_order_id' => $merchantOrderId,
            ])->throw()->json();

            return is_array($response) && data_get($response, 'id') ? $response : null;
        } catch (\Throwable $e) {
            Log::warning('Paymob merchant order inquiry failed', [
                'merchant_order_id' => $merchantOrderId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function fetchSuccessfulTransactionForPaymobOrder(string $paymobOrderId): ?array
    {
        try {
            $orderData = Http::timeout(30)->get(
                "{$this->baseUrl}/api/ecommerce/orders/{$paymobOrderId}",
                ['token' => $this->authToken()]
            )->throw()->json();

            $transactions = collect(data_get($orderData, 'transactions', []));

            $successful = $transactions->first(function ($transaction) {
                return filter_var(data_get($transaction, 'success'), FILTER_VALIDATE_BOOLEAN)
                    && ! filter_var(data_get($transaction, 'pending', true), FILTER_VALIDATE_BOOLEAN);
            });

            return is_array($successful) ? $successful : null;
        } catch (\Throwable $e) {
            Log::warning('Paymob order inquiry failed', [
                'paymob_order_id' => $paymobOrderId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function normalizeTransactionPayload(mixed $obj): array
    {
        if (is_string($obj)) {
            $decoded = json_decode($obj, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($obj) ? $obj : [];
    }

    /**
     * Verify Paymob HMAC for transaction callback / webhook payloads.
     */
    public function verifyHmac(array $data, ?string $receivedHmac): bool
    {
        $secret = $this->hmacSecret();

        if (! $secret) {
            if (app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks')) {
                return true;
            }

            return false;
        }

        if (! $receivedHmac) {
            return false;
        }

        if (array_key_exists('amount_cents', $data) || array_key_exists('success', $data)) {
            $concat = $this->concatenateCallbackFields($data);
        } else {
            $concat = $this->concatenateTransactionFields($data);
        }

        $calculated = hash_hmac('sha512', $concat, $secret);

        return hash_equals(strtolower($calculated), strtolower($receivedHmac));
    }

    protected function concatenateTransactionFields(array $obj): string
    {
        $fields = [
            data_get($obj, 'amount_cents', ''),
            data_get($obj, 'created_at', ''),
            data_get($obj, 'currency', ''),
            data_get($obj, 'error_occured', ''),
            data_get($obj, 'has_parent_transaction', ''),
            data_get($obj, 'id', ''),
            data_get($obj, 'integration_id', ''),
            data_get($obj, 'is_3d_secure', ''),
            data_get($obj, 'is_auth', ''),
            data_get($obj, 'is_capture', ''),
            data_get($obj, 'is_refunded', ''),
            data_get($obj, 'is_standalone_payment', ''),
            data_get($obj, 'is_voided', ''),
            data_get($obj, 'order.id', ''),
            data_get($obj, 'owner', ''),
            data_get($obj, 'pending', ''),
            data_get($obj, 'source_data.pan', ''),
            data_get($obj, 'source_data.sub_type', ''),
            data_get($obj, 'source_data.type', ''),
            data_get($obj, 'success', ''),
        ];

        return implode('', array_map(fn ($value) => $this->stringifyHmacValue($value), $fields));
    }

    protected function concatenateCallbackFields(array $data): string
    {
        $keys = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];

        $parts = [];
        foreach ($keys as $key) {
            $parts[] = $this->stringifyHmacValue($data[$key] ?? '');
        }

        return implode('', $parts);
    }

    protected function stringifyHmacValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
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
