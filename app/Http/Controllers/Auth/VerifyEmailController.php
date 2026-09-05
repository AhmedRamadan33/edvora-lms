<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\ActivityLog;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    public function store(VerifyOtpRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $this->otp->verify($user, $request->string('code'), OtpCode::PURPOSE_EMAIL_VERIFICATION)) {
            return back()->withErrors(['code' => __('This code is invalid or has expired. Please request a new one.')]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        ActivityLog::record('auth.email_verified', $user);

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
