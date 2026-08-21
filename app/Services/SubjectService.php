<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use App\Repositories\SubjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectService
{
    public function __construct(private SubjectRepository $subjects)
    {
    }

    public function listForInstructor(User $instructor, array $filters): LengthAwarePaginator
    {
        return $this->subjects->paginateForInstructor($instructor->id, $filters);
    }

    public function create(Course $course, string $name): Subject
    {
        $subject = $this->subjects->create([
            'course_id' => $course->id,
            'name' => trim($name),
        ]);

        ActivityLog::record('subject.created', $subject, ['name' => $subject->name, 'course' => $course->translation()?->title]);

        return $subject;
    }

    public function delete(Subject $subject): void
    {
        $name = $subject->name;
        $course = $subject->course;

        $this->subjects->delete($subject);

        ActivityLog::record('subject.deleted', $subject, ['name' => $name, 'course' => $course?->translation()?->title]);
    }
}
