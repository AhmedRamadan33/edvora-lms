<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\AdminCatalogService;
use App\Services\FawryService;
use App\Services\PaymobService;
use App\Services\PayPalService;
use App\Services\PayTabsService;
use App\Services\SettingService;
use App\Services\SocialAuthService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(StripeService $stripe, PaymobService $paymob, PayTabsService $paytabs, PayPalService $paypal, FawryService $fawry, SocialAuthService $social): View
    {
        $gateways = [
            'stripe' => ['configured' => $stripe->isConfigured()],
            'paymob' => ['configured' => $paymob->isConfigured()],
            'paytabs' => ['configured' => $paytabs->isConfigured()],
            'paypal' => ['configured' => $paypal->isConfigured()],
            'fawry' => ['configured' => $fawry->isConfigured()],
        ];

        $socialProviders = [
            'google' => ['configured' => $social->isConfigured('google')],
            'facebook' => ['configured' => $social->isConfigured('facebook')],
        ];

        $settings = SettingService::many([
            'platform_name' => 'Edvora',
            'platform_email' => config('mail.from.address', 'info@edvora.codeversetechno.com'),
            'platform_phone' => '+01199676020',
            'default_commission' => config('edvora.default_commission'),
            'currency' => config('edvora.currency'),
            'vdocipher_api_secret' => '',
            'stripe_key' => '',
            'stripe_secret' => '',
            'stripe_webhook_secret' => '',
            'paymob_api_key' => '',
            'paymob_integration_id' => '',
            'paymob_iframe_id' => '',
            'paymob_hmac_secret' => '',
            'paytabs_profile_id' => '',
            'paytabs_server_key' => '',
            'paytabs_region' => 'egypt',
            'paypal_client_id' => '',
            'paypal_secret' => '',
            'paypal_webhook_id' => '',
            'paypal_mode' => 'sandbox',
            'paypal_settlement_currency' => 'USD',
            'paypal_exchange_rate' => '',
            'zoom_client_id' => '',
            'zoom_client_secret' => '',
            'google_meet_client_id' => '',
            'google_meet_client_secret' => '',
            'stripe_enabled' => true,
            'paymob_enabled' => true,
            'paytabs_enabled' => true,
            'paypal_enabled' => true,
            'fawry_merchant_code' => '',
            'fawry_security_key' => '',
            'fawry_mode' => 'sandbox',
            'fawry_enabled' => true,
            'google_client_id' => '',
            'google_client_secret' => '',
            'google_login_enabled' => true,
            'facebook_client_id' => '',
            'facebook_client_secret' => '',
            'facebook_login_enabled' => true,
            'mail_host' => '',
            'mail_port' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => '',
            'mail_from_name' => '',
        ]);

        $paypalCurrencies = PayPalService::supportedCurrencies();

        return view('admin.settings.edit', compact('settings', 'gateways', 'socialProviders', 'paypalCurrencies'));
    }

    public function update(UpdateSettingsRequest $request, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->updateSettings($request->validated());

        return back()->with('success', __('Settings saved successfully.'));
    }
}
