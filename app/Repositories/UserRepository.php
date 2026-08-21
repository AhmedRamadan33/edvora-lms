<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function allStudents(): Collection
    {
        return $this->query()
            ->role('student')
            ->orderBy('name')
            ->get();
    }

    public function studentsForInstructor(int $instructorId): Collection
    {
        return $this->query()
            ->role('student')
            ->whereHas('enrollments.course', fn ($query) => $query->where('instructor_id', $instructorId))
            ->orderBy('name')
            ->get();
    }
}
