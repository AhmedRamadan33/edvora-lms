<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTranslation;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ExamSeeder extends Seeder
{
    public function run(ExamService $exams): void
    {
        $course = $this->resolveCourse();

        if (! $course) {
            $this->command?->warn('ExamSeeder: course "Mobile App Security" not found - skipping.');

            return;
        }

        $instructor = User::query()->find($course->instructor_id);

        if (! $instructor) {
            $this->command?->warn('ExamSeeder: course instructor not found - skipping.');

            return;
        }

        $subjectIds = Subject::query()
            ->where('course_id', $course->id)
            ->pluck('id', 'name');

        foreach ($this->examsData($subjectIds) as $data) {
            $result = $exams->create($course, $instructor, $data);

            $exams->toggleStatus($result['exam']); // publish so it is visible to enrolled students

            foreach ($result['shortfalls'] as $shortfall) {
                $this->command?->warn(sprintf(
                    'ExamSeeder: "%s" short on %s/%s (needed %d, got %d).',
                    $data['title'],
                    $shortfall['subject'],
                    $shortfall['type'],
                    $shortfall['requested'],
                    $shortfall['added'],
                ));
            }
        }

        $this->command?->info('ExamSeeder: seeded 3 published exams for "Mobile App Security".');
    }

    protected function resolveCourse(): ?Course
    {
        $translation = CourseTranslation::query()->where('title', 'Mobile App Security')->first();

        return $translation?->course;
    }

    protected function examsData(Collection $subjectIds): array
    {
        $subjects = $subjectIds->keys()->all();

        return [
            [
                'title' => 'Mobile App Security - Quick Practice Quiz',
                'duration_minutes' => 15,
                'pass_percent' => 50,
                'rules' => $this->rulesFor($subjectIds, $subjects, [
                    'mcq_single' => 3,
                    'true_false' => 2,
                ]),
            ],
            [
                'title' => 'Mobile App Security - Midterm Exam',
                'duration_minutes' => 45,
                'pass_percent' => 60,
                'rules' => $this->rulesFor($subjectIds, $subjects, [
                    'mcq_single' => 3,
                    'true_false' => 2,
                    'matching' => 1,
                ]),
            ],
            [
                'title' => 'Mobile App Security - Final Exam',
                'duration_minutes' => 90,
                'pass_percent' => 70,
                'rules' => $this->rulesFor($subjectIds, $subjects, [
                    'mcq_single' => 4,
                    'true_false' => 3,
                    'matching' => 3,
                    'fill_blank' => 2,
                    'essay' => 2,
                ]),
            ],
        ];
    }

    protected function rulesFor(Collection $subjectIds, array $subjects, array $typeCounts): array
    {
        $rules = [];

        foreach ($subjects as $subjectName) {
            $subjectId = $subjectIds[$subjectName] ?? null;

            if (! $subjectId) {
                continue;
            }

            foreach ($typeCounts as $type => $count) {
                $rules[] = ['subject_id' => $subjectId, 'type' => $type, 'count' => $count];
            }
        }

        return $rules;
    }
}
