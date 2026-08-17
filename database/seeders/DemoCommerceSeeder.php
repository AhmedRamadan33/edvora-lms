<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAnswer;
use App\Models\CourseQuestion;
use App\Models\CourseTranslation;
use App\Models\Coupon;
use App\Models\PayoutRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\Concerns\SeedsDemoHelpers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCommerceSeeder extends Seeder
{
    use SeedsDemoHelpers;

    public function run(): void
    {
        $sara = User::query()->where('email', 'instructor@edvora.test')->first();
        $ahmed = User::query()->where('email', 'ahmed@edvora.test')->first();
        $omar = User::query()->where('email', 'student@edvora.test')->first();
        $lina = User::query()->where('email', 'lina@edvora.test')->first();
        $youssef = User::query()->where('email', 'youssef@edvora.test')->first();
        $admin = User::query()->where('email', 'admin@edvora.test')->first();

        if (! $sara || ! $ahmed || ! $omar || ! $lina || ! $youssef) {
            return;
        }

        $laravel = Course::query()->where('slug', 'web-development-laravel-from-scratch')->first()
            ?? Course::query()->where('status', 'published')->where('instructor_id', $sara->id)->first();
        $bootstrap = Course::query()->where('slug', 'web-development-bootstrap-dashboards')->first()
            ?? Course::query()->where('status', 'published')->skip(1)->first();
        $ui = Course::query()->where('slug', 'ui-ux-design-arabic-rtl-ux')->first()
            ?? Course::query()->where('status', 'published')->where('instructor_id', $ahmed->id)->first();

        if (! $laravel || ! $bootstrap || ! $ui) {
            return;
        }

        $order1 = $this->paidOrder($omar, [
            ['course' => $laravel, 'instructor' => $laravel->instructor, 'price' => (float) $laravel->price, 'rate' => 15],
        ], 'stripe', 'EDV-DEMO0001');

        $this->enroll($omar, $laravel, $order1, 40);
        $this->seedLessonProgress($omar, $laravel, completeFirst: true);

        $mobileSecurity = CourseTranslation::query()->where('title', 'Mobile App Security')->first()?->course;

        if ($mobileSecurity) {
            $order3 = $this->paidOrder($omar, [
                ['course' => $mobileSecurity, 'instructor' => $mobileSecurity->instructor, 'price' => (float) $mobileSecurity->price, 'rate' => 15],
            ], 'stripe', 'EDV-DEMO0003');

            $this->enroll($omar, $mobileSecurity, $order3, 0);
        }

        $order2 = $this->paidOrder($lina, [
            ['course' => $bootstrap, 'instructor' => $bootstrap->instructor, 'price' => (float) $bootstrap->price, 'rate' => 15],
            ['course' => $ui, 'instructor' => $ui->instructor, 'price' => (float) $ui->price, 'rate' => 18],
        ], 'paymob', 'EDV-DEMO0002', 10);

        if ($discountCoupon = Coupon::query()->where('code', 'FLAT10')->first()) {
            $order2->update(['coupon_id' => $discountCoupon->id]);
        }

        $this->enroll($lina, $bootstrap, $order2, 100, completed: true);
        $this->enroll($lina, $ui, $order2, 25);
        $this->seedLessonProgress($lina, $bootstrap, completeAll: true);
        $this->seedLessonProgress($lina, $ui, completeFirst: true);

        Certificate::query()->updateOrCreate(
            ['user_id' => $lina->id, 'course_id' => $bootstrap->id],
            ['uuid' => (string) Str::uuid(), 'code' => 'CERTBOOT01', 'issued_at' => now()->subDays(2)]
        );

        Review::query()->updateOrCreate(
            ['user_id' => $lina->id, 'course_id' => $bootstrap->id],
            ['rating' => 5, 'comment' => 'Clear lessons and great dashboard examples.']
        );
        Review::query()->updateOrCreate(
            ['user_id' => $omar->id, 'course_id' => $laravel->id],
            ['rating' => 4, 'comment' => 'ممتاز جدًا لفهم فكرة بناء منتجات Laravel حقيقية.']
        );

        CartItem::query()->updateOrCreate(['user_id' => $youssef->id, 'course_id' => $laravel->id]);
        CartItem::query()->updateOrCreate(['user_id' => $youssef->id, 'course_id' => $ui->id]);
        Wishlist::query()->updateOrCreate(['user_id' => $youssef->id, 'course_id' => $bootstrap->id]);
        Wishlist::query()->updateOrCreate(['user_id' => $omar->id, 'course_id' => $ui->id]);

        $firstLesson = $laravel->lessons()->orderBy('sort_order')->first();
        $question = CourseQuestion::query()->updateOrCreate(
            [
                'course_id' => $laravel->id,
                'user_id' => $omar->id,
                'title' => 'How does commission work?',
            ],
            [
                'lesson_id' => $firstLesson?->id,
                'body' => 'Does the platform take commission before or after coupon discount?',
            ]
        );
        CourseAnswer::query()->updateOrCreate(
            ['course_question_id' => $question->id, 'user_id' => $sara->id],
            ['body' => 'Commission is calculated on the final item price after discount allocation.']
        );

        PayoutRequest::query()->updateOrCreate(
            ['instructor_id' => $sara->id, 'amount' => 120, 'status' => 'pending'],
            ['method' => 'PayPal', 'account_details' => 'sara@paypal.test']
        );

        PayoutRequest::query()->updateOrCreate(
            ['instructor_id' => $ahmed->id, 'amount' => 85, 'status' => 'paid'],
            [
                'method' => 'Bank Transfer',
                'account_details' => 'CIB - 1234567890',
                'admin_note' => 'Paid via demo transfer',
                'processed_at' => now()->subDay(),
            ]
        );

        if ($admin) {
            ActivityLog::query()->create([
                'user_id' => $admin->id,
                'action' => 'demo.seeded',
                'properties' => ['message' => 'Full marketplace demo dataset loaded'],
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
