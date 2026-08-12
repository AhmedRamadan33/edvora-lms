<?php

namespace Database\Seeders\Concerns;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorEarning;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait SeedsDemoHelpers
{
    protected function makeUser(string $name, string $email, string $locale, array $roles, ?string $bio = null): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'locale' => $locale,
                'email_verified_at' => now(),
                'is_active' => true,
                'bio' => $bio ?? 'Edvora marketplace demo account',
            ]
        );
        $user->syncRoles($roles);

        return $user;
    }

    protected function createCourseRecord(array $data): Course
    {
        $course = Course::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'instructor_id' => $data['instructor']->id,
                'category_id' => $data['category']->id,
                'level' => $data['level'] ?? 'beginner',
                'language' => $data['language'] ?? 'en',
                'price' => $data['price'],
                'currency' => \App\Services\SettingService::currency(),
                'status' => $data['status'] ?? 'published',
                'is_featured' => $data['featured'] ?? false,
                'published_at' => ($data['status'] ?? 'published') === 'published'
                    ? now()->subDays(rand(2, 40))
                    : null,
                'students_count' => $data['students_count'] ?? 0,
                'avg_rating' => $data['avg_rating'] ?? 0,
                'reviews_count' => $data['reviews_count'] ?? 0,
            ]
        );

        $course->translations()->updateOrCreate(['locale' => 'en'], [
            'title' => $data['title_en'],
            'subtitle' => $data['subtitle_en'] ?? null,
            'description' => $data['description_en'] ?? null,
        ]);
        $course->translations()->updateOrCreate(['locale' => 'ar'], [
            'title' => $data['title_ar'],
            'subtitle' => $data['subtitle_ar'] ?? null,
            'description' => $data['description_ar'] ?? null,
        ]);

        return $course->fresh(['translations']);
    }

    protected function buildSimpleCurriculum(Course $course, ?string $sectionTitle = null): void
    {
        $title = $sectionTitle ?: 'Course Content';
        $section = Section::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => $title],
            ['sort_order' => 1]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section->id, 'title' => 'Introduction'],
            [
                'type' => 'article',
                'content' => '<p>Welcome to <strong>'.$course->translation('en')?->title.'</strong>.</p><p>This lesson introduces the learning outcomes and how to follow the course.</p>',
                'is_preview' => true,
                'sort_order' => 1,
                'duration_seconds' => 240,
            ]
        );

        $videoLesson = Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section->id, 'title' => 'Core Concepts'],
            [
                'type' => 'video',
                'content' => 'Main video lesson for this course.',
                'is_preview' => false,
                'sort_order' => 2,
                'duration_seconds' => 720,
            ]
        );

        Video::query()->updateOrCreate(
            ['lesson_id' => $videoLesson->id],
            [
                'bunny_video_id' => (string) Str::uuid(),
                'status' => 'ready',
                'title' => 'Core Concepts',
                'length_seconds' => 720,
            ]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section->id, 'title' => 'Practice & Wrap-up'],
            [
                'type' => 'article',
                'content' => '<p>Apply what you learned and mark this lesson complete to update your progress.</p>',
                'is_preview' => false,
                'sort_order' => 3,
                'duration_seconds' => 180,
            ]
        );
    }

    protected function buildFullCurriculum(Course $course): void
    {
        $section1 = Section::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Getting Started'],
            ['sort_order' => 1]
        );
        $section2 = Section::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Core Skills'],
            ['sort_order' => 2]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section1->id, 'title' => 'Welcome & Overview'],
            [
                'type' => 'article',
                'content' => '<p><strong>Welcome.</strong></p><p>Learn the roadmap, tools, and expected outcomes for this professional course.</p>',
                'is_preview' => true,
                'sort_order' => 1,
                'duration_seconds' => 300,
            ]
        );

        $videoLesson = Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section1->id, 'title' => 'Hands-on Demo'],
            [
                'type' => 'video',
                'content' => 'Watermarked secure player demo lesson.',
                'is_preview' => false,
                'sort_order' => 2,
                'duration_seconds' => 900,
            ]
        );
        Video::query()->updateOrCreate(
            ['lesson_id' => $videoLesson->id],
            [
                'bunny_video_id' => (string) Str::uuid(),
                'status' => 'ready',
                'title' => 'Hands-on Demo',
                'length_seconds' => 900,
            ]
        );

        Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section2->id, 'title' => 'Resources Checklist'],
            [
                'type' => 'article',
                'content' => '<p>Checklist of tools and next steps for real-world practice.</p>',
                'is_preview' => false,
                'sort_order' => 1,
            ]
        );

        $quizLesson = Lesson::query()->updateOrCreate(
            ['course_id' => $course->id, 'section_id' => $section2->id, 'title' => 'Knowledge Check'],
            [
                'type' => 'quiz',
                'is_preview' => false,
                'sort_order' => 2,
            ]
        );

        $quiz = Quiz::query()->updateOrCreate(
            ['lesson_id' => $quizLesson->id],
            ['title' => 'Course Basics Quiz', 'pass_percent' => 70]
        );

        Question::query()->where('quiz_id', $quiz->id)->delete();
        Question::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'What is the best way to learn this topic?',
            'options' => ['Only watch videos', 'Practice while learning', 'Skip exercises', 'Memorize titles'],
            'correct_index' => 1,
            'sort_order' => 1,
        ]);
        Question::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'When should you mark a lesson complete?',
            'options' => ['Before watching', 'After understanding the concept', 'Never', 'Only on certificates'],
            'correct_index' => 1,
            'sort_order' => 2,
        ]);
    }

    protected function paidOrder(User $buyer, array $items, string $provider, string $number, float $discount = 0): Order
    {
        $subtotal = collect($items)->sum('price');
        $total = max($subtotal - $discount, 0);

        $order = Order::query()->updateOrCreate(
            ['number' => $number],
            [
                'user_id' => $buyer->id,
                'coupon_id' => null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'currency' => \App\Services\SettingService::currency(),
                'status' => 'paid',
                'payment_method' => $provider,
            ]
        );

        OrderItem::query()->where('order_id', $order->id)->delete();

        foreach ($items as $item) {
            $price = (float) $item['price'];
            $rate = (float) $item['rate'];
            $platform = round($price * ($rate / 100), 2);
            $instructorAmount = round($price - $platform, 2);

            $orderItem = OrderItem::query()->create([
                'order_id' => $order->id,
                'course_id' => $item['course']->id,
                'instructor_id' => $item['instructor']->id,
                'price' => $price,
                'commission_rate' => $rate,
                'platform_earning' => $platform,
                'instructor_earning' => $instructorAmount,
            ]);

            InstructorEarning::query()->create([
                'instructor_id' => $item['instructor']->id,
                'order_item_id' => $orderItem->id,
                'course_id' => $item['course']->id,
                'amount' => $instructorAmount,
                'status' => 'available',
            ]);
        }

        Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'provider' => $provider,
                'provider_reference' => 'demo_'.$number,
                'amount' => $total,
                'currency' => \App\Services\SettingService::currency(),
                'status' => 'paid',
                'payload' => ['demo' => true],
            ]
        );

        return $order;
    }

    protected function enroll(User $user, Course $course, Order $order, float $progress, bool $completed = false): void
    {
        Enrollment::query()->updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'order_id' => $order->id,
                'enrolled_at' => now()->subDays(3),
                'progress_percent' => $progress,
                'completed_at' => $completed ? now()->subDay() : null,
            ]
        );
    }

    protected function seedLessonProgress(User $user, Course $course, bool $completeFirst = false, bool $completeAll = false): void
    {
        $lessons = $course->lessons()->orderBy('sort_order')->get();
        foreach ($lessons as $index => $lesson) {
            $done = $completeAll || ($completeFirst && $index === 0);
            if (! $done) {
                continue;
            }

            LessonProgress::query()->updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                [
                    'course_id' => $course->id,
                    'last_position_seconds' => 120,
                    'is_completed' => true,
                    'completed_at' => now()->subDay(),
                ]
            );
        }
    }
}
