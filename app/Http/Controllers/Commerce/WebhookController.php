<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Services\PaymobService;
use App\Services\PayPalService;
use App\Services\PayTabsService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request, StripeService $stripe): Response
    {
        try {
            $stripe->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error', ['message' => $e->getMessage()]);

            return response('invalid', 400);
        }

        return response('ok');
    }

    public function paymob(Request $request, PaymobService $paymob): Response
    {
        try {
            $payload = $request->json()->all();
            if ($payload === []) {
                $payload = $request->all();
            }

            $paymob->handleWebhook(
                $payload,
                $request->query('hmac') ?? data_get($payload, 'hmac')
            );
        } catch (\Throwable $e) {
            Log::error('Paymob webhook error', [
                'message' => $e->getMessage(),
                'query' => $request->query(),
                'payload' => $request->all(),
            ]);

            return response('invalid', 400);
        }

        return response('ok');
    }

    public function paytabs(Request $request, PayTabsService $paytabs): Response
    {
        try {
            $paytabs->handleWebhook($request->getContent(), $request->header('Signature'));
        } catch (\Throwable $e) {
            Log::error('PayTabs webhook error', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response('invalid', 400);
        }

        return response('ok');
    }

    public function paypal(Request $request, PayPalService $paypal): Response
    {
        try {
            $body = $request->json()->all();
            if ($body === []) {
                $body = $request->all();
            }

            $paypal->handleWebhook([
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            ], $body);
        } catch (\Throwable $e) {
            Log::error('PayPal webhook error', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response('invalid', 400);
        }

        return response('ok');
    }
}
