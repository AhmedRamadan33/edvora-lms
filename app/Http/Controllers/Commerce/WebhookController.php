<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Services\PaymobService;
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
            $paymob->handleWebhook(
                $request->all(),
                $request->query('hmac') ?? $request->input('hmac')
            );
        } catch (\Throwable $e) {
            Log::error('Paymob webhook error', ['message' => $e->getMessage()]);

            return response('invalid', 400);
        }

        return response('ok');
    }
}
