<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    public function issueIfEligible(User $user, Course $course): ?Certificate
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment || (float) $enrollment->progress_percent < 100) {
            return null;
        }

        return Certificate::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            ['issued_at' => now()]
        );
    }

    public function download(Certificate $certificate)
    {
        $certificate->load(['user', 'course.translations', 'course.instructor']);

        $courseTitle = $certificate->course->translation('en')?->title
            ?: $certificate->course->translation()?->title;

        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'platform' => SettingService::platformName(),
            'courseTitle' => $courseTitle,
            'instructorName' => $certificate->course->instructor?->name ?? SettingService::platformName(),
            'verificationUrl' => route('certificates.verify', $certificate->code),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('certificate-'.$certificate->code.'.pdf');
    }

    public function findByCode(string $code): ?Certificate
    {
        return Certificate::query()
            ->where('code', $code)
            ->with(['user', 'course.translations'])
            ->first();
    }
}
