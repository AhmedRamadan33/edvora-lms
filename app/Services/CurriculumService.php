<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Video;
use Illuminate\Http\UploadedFile;

class CurriculumService
{
    public function __construct(private VdoCipherService $vdocipher)
    {
    }

    public function addSection(Course $course, string $title): Section
    {
        $section = $course->sections()->create([
            'title' => $title,
            'sort_order' => $course->sections()->count() + 1,
        ]);

        ActivityLog::record('section.created', $section, ['title' => $title, 'course' => $course->translation()?->title]);

        return $section;
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
            $this->attachVideo($lesson, $data['title'], $data['video_id']);
        }

        if ($data['type'] === 'quiz') {
            $this->attachQuiz($lesson, $data);
        }

        ActivityLog::record('lesson.created', $lesson, ['title' => $lesson->title, 'course' => $course->translation()?->title]);

        return $lesson;
    }

    public function deleteLesson(Course $course, Lesson $lesson): void
    {
        abort_unless($lesson->course_id === $course->id, 404);

        if ($lesson->video?->vdocipher_video_id) {
            $this->vdocipher->deleteVideo($lesson->video->vdocipher_video_id);
        }

        $lesson->delete();

        ActivityLog::record('lesson.deleted', $lesson, ['title' => $lesson->title, 'course' => $course->translation()?->title]);
    }

    public function requestVideoUploadCredentials(string $title): array
    {
        return $this->vdocipher->getUploadCredentials($title);
    }

    public function checkVideoStatus(Course $course, Lesson $lesson): Video
    {
        abort_unless($lesson->course_id === $course->id && $lesson->video, 404);

        $video = $lesson->video;

        try {
            $result = $this->vdocipher->checkStatus($video->vdocipher_video_id);
            $video->update(['status' => $this->vdocipher->mapRemoteStatus($result['status'] ?? null)]);
        } catch (\Throwable) {
            return $video;
        }

        return $video->fresh();
    }

    protected function attachVideo(Lesson $lesson, string $title, string $videoId): void
    {
        Video::query()->create([
            'lesson_id' => $lesson->id,
            'vdocipher_video_id' => $videoId,
            'status' => str_starts_with($videoId, 'demo-') ? Video::STATUS_READY : Video::STATUS_PROCESSING,
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
