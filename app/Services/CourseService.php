<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Repositories\CourseRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseService
{
    public function __construct(private CourseRepository $courses)
    {
    }

    public function paginateForInstructor(User $instructor, int $perPage = 12, ?string $search = null)
    {
        return $this->courses->paginateForInstructor($instructor->id, $perPage, $search);
    }

    public function activeCategories()
    {
        return $this->courses->activeCategories();
    }

    public function create(User $instructor, array $data, ?UploadedFile $thumbnail = null): Course
    {
        /** @var Course $course */
        $course = $this->courses->create([
            'instructor_id' => $instructor->id,
            'category_id' => $data['category_id'] ?? null,
            'slug' => Str::slug($data['title_en']).'-'.Str::lower(Str::random(5)),
            'thumbnail' => $thumbnail?->store('courses', 'public'),
            'level' => $data['level'],
            'language' => $data['language'],
            'price' => $data['price'],
            'currency' => SettingService::currency(),
            'status' => 'draft',
        ]);

        $this->syncTranslations($course, $data);

        ActivityLog::record('course.created', $course, ['title' => $data['title_en']]);

        return $course;
    }

    public function update(Course $course, array $data, ?UploadedFile $thumbnail = null): Course
    {
        $payload = [
            'category_id' => $data['category_id'] ?? null,
            'level' => $data['level'],
            'language' => $data['language'],
            'price' => $data['price'],
            'currency' => SettingService::currency(),
        ];

        if ($thumbnail) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $payload['thumbnail'] = $thumbnail->store('courses', 'public');
        }

        $this->courses->update($course, $payload);
        $this->syncTranslations($course, $data);

        ActivityLog::record('course.updated', $course, ['title' => $data['title_en'] ?? $course->translation()?->title]);

        return $course->refresh();
    }

    public function submitForReview(Course $course): array
    {
        if ($course->lessons()->count() === 0) {
            return ['ok' => false, 'message' => __('Add at least one lesson before submitting.')];
        }

        $this->courses->update($course, [
            'status' => 'pending_review',
            'rejection_reason' => null,
        ]);

        ActivityLog::record('course.submitted', $course, ['title' => $course->translation()?->title]);

        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new GenericNotification(
                __(':instructor submitted ":title" for review.', [
                    'instructor' => $course->instructor?->name,
                    'title' => $course->translation()?->title,
                ]),
                route('admin.courses.show', $course),
                __('Course submitted for review')
            ));
        }

        return ['ok' => true, 'message' => __('Course submitted for review.')];
    }

    public function loadForEdit(Course $course): Course
    {
        return $this->courses->loadCurriculum($course);
    }

    protected function syncTranslations(Course $course, array $data): void
    {
        foreach (['en', 'ar'] as $locale) {
            $course->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $data["title_{$locale}"],
                    'subtitle' => $data["subtitle_{$locale}"] ?? null,
                    'description' => $data["description_{$locale}"] ?? null,
                ]
            );
        }
    }
}
