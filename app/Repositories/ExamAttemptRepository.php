<?php

namespace App\Repositories;

use App\Models\ExamAttempt;

class ExamAttemptRepository extends BaseRepository
{
    public function __construct(ExamAttempt $model)
    {
        parent::__construct($model);
    }

    public function forExamAndUser(int $examId, int $userId): ?ExamAttempt
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->where('user_id', $userId)
            ->first();
    }
}
