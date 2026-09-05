<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    private const CODE_MIN = 100000;

    private const CODE_MAX = 999999;

    private const EXPIRES_IN_MINUTES = 10;

    private const RESEND_COOLDOWN_SECONDS = 60;

    public function generateAndSend(User $user, string $purpose): OtpCode
    {
        $code = (string) random_int(self::CODE_MIN, self::CODE_MAX);

        $otp = OtpCode::query()->create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
        ]);

        $user->notify(new OtpCodeNotification($code, self::EXPIRES_IN_MINUTES));

        return $otp;
    }

    public function verify(User $user, string $code, string $purpose): bool
    {
        $otp = $this->latestFor($user, $purpose);

        if (! $otp || $otp->isConsumed() || $otp->isExpired() || $otp->hasExceededAttempts()) {
            return false;
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }

    public function canResend(User $user, string $purpose): bool
    {
        return $this->secondsUntilResend($user, $purpose) === 0;
    }

    public function secondsUntilResend(User $user, string $purpose): int
    {
        $latest = $this->latestFor($user, $purpose);

        if (! $latest) {
            return 0;
        }

        $availableAt = $latest->created_at->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        return max(0, $availableAt->getTimestamp() - now()->getTimestamp());
    }

    private function latestFor(User $user, string $purpose): ?OtpCode
    {
        return OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();
    }
}
