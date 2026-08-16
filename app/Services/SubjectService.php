<?php

namespace App\Services;

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
        return $this->subjects->create([
            'course_id' => $course->id,
            'name' => trim($name),
        ]);
    }

    public function delete(Subject $subject): void
    {
        $this->subjects->delete($subject);
    }
}
