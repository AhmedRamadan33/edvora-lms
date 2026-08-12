<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoHelpers;
use Database\Seeders\Data\CourseCatalog;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use SeedsDemoHelpers;

    public function run(): void
    {
        $instructors = User::query()
            ->whereIn('email', [
                'instructor@edvora.test',
                'ahmed@edvora.test',
                'nour@edvora.test',
                'layla@edvora.test',
                'karim@edvora.test',
            ])
            ->get()
            ->values();

        if ($instructors->isEmpty()) {
            return;
        }

        $instructorIndex = 0;
        $fullCurriculumEvery = 20; // richer curriculum for every Nth course
        $created = 0;

        foreach (CourseCatalog::categories() as $categoryData) {
            $category = Category::query()->where('slug', $categoryData['slug'])->first();
            if (! $category) {
                continue;
            }

            foreach ($categoryData['courses'] as $courseData) {
                $instructor = $instructors[$instructorIndex % $instructors->count()];
                $instructorIndex++;

                $students = rand(12, 420);
                $reviews = rand(3, max(3, (int) ($students / 8)));
                $rating = round(mt_rand(35, 50) / 10, 2);

                $course = $this->createCourseRecord([
                    'instructor' => $instructor,
                    'category' => $category,
                    'slug' => $categoryData['slug'].'-'.$courseData['slug'],
                    'level' => $courseData['level'],
                    'price' => $courseData['price'],
                    'status' => 'published',
                    'featured' => (bool) ($courseData['featured'] ?? false),
                    'title_en' => $courseData['title_en'],
                    'title_ar' => $courseData['title_ar'],
                    'subtitle_en' => $courseData['subtitle_en'],
                    'subtitle_ar' => $courseData['subtitle_ar'],
                    'description_en' => $courseData['description_en'],
                    'description_ar' => $courseData['description_ar'],
                    'students_count' => $students,
                    'avg_rating' => $rating,
                    'reviews_count' => $reviews,
                    'language' => $instructorIndex % 3 === 0 ? 'ar' : 'en',
                ]);

                $created++;
                if ($created % $fullCurriculumEvery === 1 || ($courseData['featured'] ?? false)) {
                    $this->buildFullCurriculum($course);
                } else {
                    $this->buildSimpleCurriculum($course, 'Course Modules');
                }
            }
        }

        // Extra demo states: one draft + one pending review for admin/instructor panels
        $sara = User::query()->where('email', 'instructor@edvora.test')->first();
        $ahmed = User::query()->where('email', 'ahmed@edvora.test')->first();
        $business = Category::query()->where('slug', 'business-entrepreneurship')->first();
        $languages = Category::query()->where('slug', 'languages')->first();

        if ($sara && $business) {
            $draft = $this->createCourseRecord([
                'instructor' => $sara,
                'category' => $business,
                'slug' => 'pricing-strategy-draft',
                'level' => 'advanced',
                'price' => 79.00,
                'status' => 'draft',
                'featured' => false,
                'title_en' => 'Course Pricing Strategy (Draft)',
                'title_ar' => 'استراتيجية تسعير الكورسات (مسودة)',
                'subtitle_en' => 'Not submitted yet',
                'subtitle_ar' => 'لم يُرسل بعد',
                'description_en' => 'Draft course for instructor panel testing.',
                'description_ar' => 'كورس مسودة لتجربة لوحة المدرّس.',
            ]);
            $this->buildSimpleCurriculum($draft, 'Pricing Intro');
        }

        if ($ahmed && $languages) {
            $pending = $this->createCourseRecord([
                'instructor' => $ahmed,
                'category' => $languages,
                'slug' => 'english-for-developers-pending',
                'level' => 'beginner',
                'price' => 19.99,
                'status' => 'pending_review',
                'featured' => false,
                'title_en' => 'English for Developers (Pending Review)',
                'title_ar' => 'الإنجليزية للمبرمجين (قيد المراجعة)',
                'subtitle_en' => 'Waiting for admin approval',
                'subtitle_ar' => 'بانتظار موافقة الأدمن',
                'description_en' => 'Pending course so admin can approve/reject in demo.',
                'description_ar' => 'كورس قيد المراجعة ليوافق عليه الأدمن أو يرفضه.',
            ]);
            $this->buildSimpleCurriculum($pending, 'Getting Started');
        }
    }
}
