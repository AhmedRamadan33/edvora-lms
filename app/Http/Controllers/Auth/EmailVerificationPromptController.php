<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __construct(private OtpService $otp)
    {
    }

    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $secondsUntilResend = $this->otp->secondsUntilResend($user, OtpCode::PURPOSE_EMAIL_VERIFICATION);

        return view('auth.verify-email', compact('secondsUntilResend'));
    }
}
