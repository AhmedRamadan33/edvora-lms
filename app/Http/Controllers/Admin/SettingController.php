<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\AdminCatalogService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = SettingService::many([
            'platform_name' => 'Edvora',
            'default_commission' => config('edvora.default_commission'),
            'currency' => config('edvora.currency'),
            'bunny_library_id' => '',
            'bunny_api_key' => '',
            'bunny_cdn_hostname' => '',
            'bunny_token_key' => '',
            'stripe_key' => '',
            'stripe_secret' => '',
            'stripe_webhook_secret' => '',
            'paymob_api_key' => '',
            'paymob_integration_id' => '',
            'paymob_iframe_id' => '',
            'paymob_hmac_secret' => '',
        ]);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->updateSettings($request->validated());

        return back()->with('success', __('Settings saved successfully.'));
    }
}
