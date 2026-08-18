<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalService
{
    public function __construct(private OrderFulfillmentService $fulfillment)
    {
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId()) && filled($this->secret());
    }

    public function isEnabledByAdmin(): bool
    {
        return (bool) SettingService::get('paypal_enabled', true);
    }

    protected function clientId(): ?string
    {
        return config('edvora.paypal.client_id') ?: SettingService::get('paypal_client_id');
    }

    protected function secret(): ?string
    {
        return config('edvora.paypal.secret') ?: SettingService::get('paypal_secret');
    }

    protected function webhookId(): ?string
    {
        return config('edvora.paypal.webhook_id') ?: SettingService::get('paypal_webhook_id');
    }

    protected function mode(): string
    {
        return config('edvora.paypal.mode') ?: (SettingService::get('paypal_mode') ?: 'sandbox');
    }

    protected function baseUrl(): string
    {
        return $this->mode() === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    protected function accessToken(): string
    {
        return Http::asForm()
            ->withBasicAuth((string) $this->clientId(), (string) $this->secret())
            ->timeout(30)
            ->post("{$this->baseUrl()}/v1/oauth2/token", ['grant_type' => 'client_credentials'])
            ->throw()->json('access_token');
    }

    public function createOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            if (! config('edvora.payments.allow_demo')) {
                throw new RuntimeException('PayPal is not configured.');
            }

            return [
                'demo' => true,
                'approve_url' => route('checkout.paypal.demo', $order),
            ];
        }

        $order->loadMissing('items.course.translations', 'user');

        $response = Http::withToken($this->accessToken())
            ->timeout(30)
            ->post("{$this->baseUrl()}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->number,
                    'custom_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => strtoupper($order->currency),
                        'value' => number_format((float) $order->total, 2, '.', ''),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'return_url' => route('checkout.paypal.return'),
                            'cancel_url' => route('checkout.cancel', $order),
                        ],
                    ],
                ],
            ])->throw()->json();

        $approveUrl = collect($response['links'] ?? [])->firstWhere('rel', 'payer-action')['href']
            ?? collect($response['links'] ?? [])->firstWhere('rel', 'approve')['href']
            ?? null;

        $this->fulfillment->markPendingPayment($order, 'paypal', $response['id'] ?? null, $response);

        return [
            'demo' => false,
            'approve_url' => $approveUrl,
            'paypal_order_id' => $response['id'] ?? null,
        ];
    }

    public function captureOrder(Order $order, string $paypalOrderId): bool
    {
        if ($order->status === 'paid') {
            return true;
        }

        if (! $this->isConfigured()) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->timeout(30)
            ->post("{$this->baseUrl()}/v2/checkout/orders/{$paypalOrderId}/capture")
            ->throw()->json();

        if (($response['status'] ?? null) !== 'COMPLETED') {
            $this->fulfillment->markFailed($order, 'paypal', $paypalOrderId, $response);

            return false;
        }

        $capture = data_get($response, 'purchase_units.0.payments.captures.0');
        $amountMinorUnits = (int) round(((float) data_get($capture, 'amount.value', 0)) * 100);
        $currency = data_get($capture, 'amount.currency_code');

        if (! $this->fulfillment->amountsMatch($order, $amountMinorUnits, $currency, 'paypal')) {
            return false;
        }

        $this->fulfillment->markPaid(
            $order->load('items.course', 'user', 'coupon'),
            'paypal',
            data_get($capture, 'id', $paypalOrderId),
            $response
        );

        return true;
    }

    public function verifyWebhookSignature(array $headers, array $body): bool
    {
        $webhookId = $this->webhookId();

        if (! $webhookId) {
            return app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks');
        }

        try {
            $result = Http::withToken($this->accessToken())
                ->timeout(30)
                ->post("{$this->baseUrl()}/v1/notifications/verify-webhook-signature", [
                    'auth_algo' => $headers['auth_algo'] ?? null,
                    'cert_url' => $headers['cert_url'] ?? null,
                    'transmission_id' => $headers['transmission_id'] ?? null,
                    'transmission_sig' => $headers['transmission_sig'] ?? null,
                    'transmission_time' => $headers['transmission_time'] ?? null,
                    'webhook_id' => $webhookId,
                    'webhook_event' => $body,
                ])->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('PayPal webhook signature verification request failed', ['message' => $e->getMessage()]);

            return false;
        }

        return ($result['verification_status'] ?? null) === 'SUCCESS';
    }

    public function handleWebhook(array $headers, array $body): void
    {
        if (! $this->verifyWebhookSignature($headers, $body)) {
            throw new RuntimeException('Invalid PayPal webhook signature.');
        }

        $type = data_get($body, 'event_type');

        if ($type !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $orderNumber = data_get($body, 'resource.supplementary_data.related_ids.order_reference_id')
            ?? data_get($body, 'resource.custom_id');

        $order = $orderNumber
            ? Order::query()->where('number', $orderNumber)->orWhere('id', $orderNumber)->with(['items.course', 'user', 'coupon'])->first()
            : null;

        if (! $order) {
            Log::warning('PayPal webhook order not found', ['resource' => data_get($body, 'resource.id')]);

            return;
        }

        $amountMinorUnits = (int) round(((float) data_get($body, 'resource.amount.value', 0)) * 100);
        $currency = data_get($body, 'resource.amount.currency_code');

        if (! $this->fulfillment->amountsMatch($order, $amountMinorUnits, $currency, 'paypal')) {
            return;
        }

        $this->fulfillment->markPaid($order, 'paypal', data_get($body, 'resource.id', ''), $body);
    }
}
