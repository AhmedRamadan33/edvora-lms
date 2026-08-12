<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Nour Adel',
                'role' => 'Frontend Developer',
                'rating' => 5,
                'content_en' => 'Edvora helped me move from tutorials to real projects. The instructors are clear and practical.',
                'content_ar' => 'إدفورا ساعدتني أنتقل من الشروحات النظرية لمشاريع حقيقية. المدرّسون واضحون وعمليّون جدًا.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Karim Hassan',
                'role' => 'Product Designer',
                'rating' => 5,
                'content_en' => 'The learning experience feels premium. Progress tracking and certificates made a real difference.',
                'content_ar' => 'تجربة التعلّم احترافية جدًا. تتبع التقدم والشهادات فرّقوا معايا بشكل واضح.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Salma Youssef',
                'role' => 'Marketing Specialist',
                'rating' => 4,
                'content_en' => 'I love the bilingual experience and how easy it is to buy and start learning immediately.',
                'content_ar' => 'حبيت دعم العربي والإنجليزي، وسهولة الشراء والبدء في التعلّم فورًا.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Hassan Mostafa',
                'role' => 'Backend Engineer',
                'rating' => 5,
                'content_en' => 'The DevOps and Laravel tracks are production-minded. I applied lessons at work the same week.',
                'content_ar' => 'مسارات DevOps وLaravel عملية جدًا. طبّقت الدروس في الشغل خلال نفس الأسبوع.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Rania Saad',
                'role' => 'Data Analyst',
                'rating' => 5,
                'content_en' => 'Clear Arabic explanations plus English resources — perfect for MENA learners.',
                'content_ar' => 'شرح عربي واضح مع مصادر إنجليزية — مثالي لمتعلمي المنطقة.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Omar Fathy',
                'role' => 'Entrepreneur',
                'rating' => 4,
                'content_en' => 'Business and pricing courses helped me package my consulting offer properly.',
                'content_ar' => 'كورسات الأعمال والتسعير ساعدتني أحزم عرض الاستشارات بشكل صحيح.',
                'sort_order' => 6,
            ],
        ];

        foreach ($testimonials as $item) {
            Testimonial::query()->updateOrCreate(
                ['name' => $item['name'], 'role' => $item['role']],
                array_merge($item, [
                    'is_published' => true,
                    'show_on_home' => $item['sort_order'] <= 3,
                ])
            );
        }
    }
}
