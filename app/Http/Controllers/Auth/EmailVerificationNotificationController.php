<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $this->otp->canResend($user, OtpCode::PURPOSE_EMAIL_VERIFICATION)) {
            $seconds = $this->otp->secondsUntilResend($user, OtpCode::PURPOSE_EMAIL_VERIFICATION);

            return back()->withErrors(['code' => __('Please wait :seconds seconds before requesting a new code.', ['seconds' => $seconds])]);
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', __('A new verification code has been sent to your email.'));
    }
}
