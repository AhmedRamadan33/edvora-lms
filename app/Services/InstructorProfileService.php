<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\InstructorEarning;
use App\Models\InstructorProfile;
use App\Models\PayoutRequest;
use App\Models\User;

class InstructorProfileService
{
    public function getOrCreate(User $user): InstructorProfile
    {
        return InstructorProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'pending']
        );
    }

    public function update(User $user, array $data): InstructorProfile
    {
        $profile = $this->getOrCreate($user);
        $profile->update($data);

        $user->syncRoles(['instructor']);

        ActivityLog::record('instructor_profile.updated', $profile);

        return $profile->refresh();
    }

    public function earningsSummary(User $instructor, int $perPage = 20): array
    {
        return [
            'earnings' => InstructorEarning::query()
                ->where('instructor_id', $instructor->id)
                ->with('course.translations')
                ->latest()
                ->paginate($perPage),
            'available' => InstructorEarning::query()
                ->where('instructor_id', $instructor->id)
                ->where('status', 'available')
                ->sum('amount'),
            'payouts' => PayoutRequest::query()
                ->where('instructor_id', $instructor->id)
                ->latest()
                ->get(),
        ];
    }
}
