<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\InstructorEarning;
use App\Models\InstructorProfile;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Notifications\CourseStatusNotification;
use App\Repositories\CouponRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminCatalogService
{
    public function __construct(
        private CouponRepository $coupons,
        private CurrencyService $currency,
    ) {
    }

    public function paginateCoupons(int $perPage = 20, ?string $search = null)
    {
        return $this->coupons->paginateLatest($perPage, $search);
    }

    public function createCoupon(array $data): Coupon
    {
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = true;

        $coupon = $this->coupons->create($data);

        ActivityLog::record('coupon.created', $coupon, ['code' => $coupon->code]);

        return $coupon;
    }

    public function deleteCoupon(Coupon $coupon): bool
    {
        $code = $coupon->code;

        $result = $this->coupons->delete($coupon);

        ActivityLog::record('coupon.deleted', $coupon, ['code' => $code]);

        return $result;
    }

    public function updateSettings(array $data): void
    {
        $previousCurrency = $this->currency->code();

        foreach ($data as $key => $value) {
            if ($key === 'currency') {
                $value = strtoupper((string) $value);
            }

            SettingService::set($key, $value);
        }

        if (isset($data['currency']) && strtoupper((string) $data['currency']) !== $previousCurrency) {
            $this->currency->syncCourses(strtoupper((string) $data['currency']));
        }

        ActivityLog::record('settings.updated', null, [
            'currency' => SettingService::currency(),
        ]);
    }

    public function approveCourse(Course $course): void
    {
        $course->update([
            'status' => 'published',
            'published_at' => now(),
            'rejection_reason' => null,
        ]);

        ActivityLog::record('course.approved', $course, ['title' => $course->translation()?->title]);
        $course->instructor->notify(new CourseStatusNotification($course, 'published'));
    }

    public function rejectCourse(Course $course, string $reason): void
    {
        $course->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        ActivityLog::record('course.rejected', $course, ['title' => $course->translation()?->title, 'reason' => $reason]);
        $course->instructor->notify(new CourseStatusNotification($course, 'rejected'));
    }

    public function approveInstructor(InstructorProfile $profile): void
    {
        $profile->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);
        $profile->user->syncRoles(['instructor']);
        ActivityLog::record('instructor.approved', $profile, ['name' => $profile->user?->name]);
    }

    public function rejectInstructor(InstructorProfile $profile, string $reason): void
    {
        $profile->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        ActivityLog::record('instructor.rejected', $profile, ['name' => $profile->user?->name, 'reason' => $reason]);
    }

    public function createInstructor(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(40)),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            $user->syncRoles(['instructor']);

            InstructorProfile::create([
                'user_id' => $user->id,
                'headline' => $data['headline'] ?? null,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            Password::sendResetLink(['email' => $user->email]);

            ActivityLog::record('instructor.created_by_admin', $user, ['name' => $user->name]);

            return $user;
        });
    }

    public function approvePayout(PayoutRequest $payout, string $transactionReference): array
    {
        if ($payout->status !== 'pending') {
            return ['ok' => false, 'message' => __('Already processed.')];
        }

        DB::transaction(function () use ($payout, $transactionReference) {
            $payout->update([
                'status' => 'paid',
                'transaction_reference' => $transactionReference,
                'processed_at' => now(),
            ]);

            InstructorEarning::query()
                ->where('instructor_id', $payout->instructor_id)
                ->where('status', 'available')
                ->orderBy('id')
                ->limit(1000)
                ->get()
                ->reduce(function ($remaining, $earning) {
                    if ($remaining <= 0) {
                        return $remaining;
                    }
                    $earning->update(['status' => 'paid']);

                    return $remaining - (float) $earning->amount;
                }, (float) $payout->amount);

            ActivityLog::record('payout.paid', $payout, ['amount' => $payout->amount]);
        });

        return ['ok' => true, 'message' => __('Payout marked as paid.')];
    }

    public function rejectPayout(PayoutRequest $payout, string $adminNote): void
    {
        $payout->update([
            'status' => 'rejected',
            'admin_note' => $adminNote,
            'processed_at' => now(),
        ]);

        ActivityLog::record('payout.rejected', $payout, ['amount' => $payout->amount, 'reason' => $adminNote]);
    }

    public function requestPayout(User $instructor, array $data): array
    {
        $available = InstructorEarning::query()
            ->where('instructor_id', $instructor->id)
            ->where('status', 'available')
            ->sum('amount');

        if ($data['amount'] > $available) {
            return ['ok' => false, 'message' => __('Amount exceeds available balance.')];
        }

        $payout = PayoutRequest::query()->create([
            'instructor_id' => $instructor->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'account_details' => $this->formatPayoutAccountDetails($data),
            'status' => 'pending',
        ]);

        ActivityLog::record('payout.requested', $payout, ['amount' => $payout->amount]);

        return ['ok' => true, 'message' => __('Payout request submitted.')];
    }

    protected function formatPayoutAccountDetails(array $data): string
    {
        return match ($data['method']) {
            'paypal' => "PayPal: {$data['paypal_email']}",
            'bank_transfer' => "Bank: {$data['bank_name']} | Account: {$data['account_number']} | Holder: {$data['account_holder']}",
            'e_wallet' => "Wallet: {$data['wallet_number']}",
        };
    }
}
