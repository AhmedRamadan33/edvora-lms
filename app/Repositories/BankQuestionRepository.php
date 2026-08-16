<?php

namespace App\Repositories;

use App\Models\BankQuestion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BankQuestionRepository extends BaseRepository
{
    public function __construct(BankQuestion $model)
    {
        parent::__construct($model);
    }

    public function paginateForCourse(int $courseId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->with(['subject', 'choices', 'matches'])
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['difficulty'] ?? null, fn ($query, $difficulty) => $query->where('difficulty', $difficulty))
            ->when($filters['subject_id'] ?? null, fn ($query, $subjectId) => $query->where('subject_id', $subjectId))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('question', 'like', "%{$search}%"))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countsByType(int $courseId): array
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type')
            ->all();
    }
}
