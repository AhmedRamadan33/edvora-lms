<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Video;
use Illuminate\Http\UploadedFile;

class CurriculumService
{
    public function __construct(private BunnyStreamService $bunny)
    {
    }

    public function addSection(Course $course, string $title): Section
    {
        return $course->sections()->create([
            'title' => $title,
            'sort_order' => $course->sections()->count() + 1,
        ]);
    }

    public function addLesson(Course $course, Section $section, array $data, ?UploadedFile $attachment = null): Lesson
    {
        abort_unless($section->course_id === $course->id, 404);

        $lesson = $section->lessons()->create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $data['type'] === 'article' ? ($data['content'] ?? null) : null,
            'attachment' => $data['type'] === 'file' ? $attachment?->store('lessons', 'public') : null,
            'is_preview' => (bool) ($data['is_preview'] ?? false),
            'sort_order' => $section->lessons()->count() + 1,
        ]);

        if ($data['type'] === 'video') {
            $this->attachVideo($lesson, $data['title']);
        }

        if ($data['type'] === 'quiz') {
            $this->attachQuiz($lesson, $data);
        }

        return $lesson;
    }

    public function deleteLesson(Course $course, Lesson $lesson): void
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $lesson->delete();
    }

    public function markVideoReady(Course $course, Lesson $lesson): void
    {
        abort_unless($lesson->course_id === $course->id && $lesson->video, 404);
        $this->bunny->markReady($lesson->video);
    }

    protected function attachVideo(Lesson $lesson, string $title): void
    {
        $created = $this->bunny->createVideo($title);

        Video::query()->create([
            'lesson_id' => $lesson->id,
            'bunny_video_id' => $created['guid'] ?? null,
            'library_id' => $this->bunny->libraryId(),
            'status' => ! empty($created['demo']) ? 'ready' : 'created',
            'title' => $title,
        ]);
    }

    protected function attachQuiz(Lesson $lesson, array $data): void
    {
        $quiz = Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'title' => $data['quiz_title'] ?: $data['title'],
            'pass_percent' => $data['pass_percent'] ?? 70,
        ]);

        foreach ($data['questions'] ?? [] as $index => $question) {
            $options = array_values(array_filter(
                $question['options'] ?? [],
                fn ($option) => filled($option)
            ));

            if (count($options) < 2) {
                continue;
            }

            $correctIndex = (int) $question['correct_index'];
            if ($correctIndex >= count($options)) {
                $correctIndex = 0;
            }

            Question::query()->create([
                'quiz_id' => $quiz->id,
                'question' => $question['question'],
                'options' => $options,
                'correct_index' => $correctIndex,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
