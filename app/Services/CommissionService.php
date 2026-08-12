<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;

class CommissionService
{
    public function rateForInstructor(User $instructor): float
    {
        $profileRate = $instructor->instructorProfile?->commission_rate;

        if ($profileRate !== null) {
            return (float) $profileRate;
        }

        return SettingService::commissionRate();
    }

    public function split(Course $course, float $price): array
    {
        $rate = $this->rateForInstructor($course->instructor);
        $platform = round($price * ($rate / 100), 2);
        $instructor = round($price - $platform, 2);

        return [
            'commission_rate' => $rate,
            'platform_earning' => $platform,
            'instructor_earning' => $instructor,
        ];
    }
}
