<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'en' => 'About Edvora',
                'ar' => 'عن إدفورا',
                'body_en' => "Edvora is a comprehensive digital learning platform designed to provide a flexible and engaging educational experience, bringing students and instructors together in an easy-to-use learning environment. The platform offers a wide range of educational courses across different fields, allowing learners to access lessons, videos, quizzes, and other learning materials in an organized and effective way. Edvora also enables instructors to create and sell their own courses, manage their educational content and students, and share their knowledge with a wider audience. Our goal at Edvora is to build a modern learning experience that makes education more accessible and flexible, helping every learner develop new skills, expand their knowledge, and achieve their educational and professional goals.",
                'body_ar' => "إدڤورا هي منصة تعليمية رقمية متكاملة تهدف إلى توفير تجربة تعليمية مرنة ومميزة تجمع بين الطلاب والمدرسين في بيئة تعليمية سهلة الاستخدام. توفر المنصة مجموعة متنوعة من الكورسات التعليمية في مجالات مختلفة، مع إمكانية متابعة المحتوى التعليمي من خلال الدروس والفيديوهات والاختبارات، بما يساعد المتعلمين على تطوير مهاراتهم واكتساب المعرفة بطريقة منظمة وفعّالة. كما تتيح إدڤورا للمدرسين إنشاء وبيع الكورسات الخاصة بهم، وإدارة المحتوى التعليمي والطلاب، مما يوفر لهم فرصة للوصول إلى عدد أكبر من المتعلمين وتحويل خبراتهم ومعرفتهم إلى محتوى تعليمي يستفيد منه الآخرون. نسعى في إدڤورا إلى بناء تجربة تعليمية حديثة تجعل التعلم أكثر سهولة ومرونة، وتساعد كل متعلم على التطور، اكتساب مهارات جديدة، وتحقيق أهدافه التعليمية والمهنية. ولو عايزها بنفس شكل الصفحة اللي عندك، بحيث تكون 3 أو 4 فقرات قصيرة بدل النص الطويل عشان الشكل يبقى أنضف، أقدر أصيغها لك بشكل مناسب جدًا للـ UI.",
            ],
            [
                'slug' => 'terms',
                'en' => 'Terms of Service',
                'ar' => 'الشروط والأحكام',
                'body_en' => "By using Edvora you agree to marketplace policies for buying, teaching, and content review.\nInstructors must submit courses for review before publishing.",
                'body_ar' => "باستخدام إدفورا فإنك توافق على سياسات السوق للشراء والتدريس ومراجعة المحتوى.\nيجب على المدرّسين إرسال الكورسات للمراجعة قبل النشر.",
            ],
            [
                'slug' => 'privacy',
                'en' => 'Privacy Policy',
                'ar' => 'سياسة الخصوصية',
                'body_en' => 'We store account, enrollment, and payment metadata required to operate the learning marketplace.',
                'body_ar' => 'نخزّن بيانات الحساب والاشتراك والدفع اللازمة لتشغيل السوق التعليمي.',
            ],
            [
                'slug' => 'faq',
                'en' => 'FAQ',
                'ar' => 'الأسئلة الشائعة',
                'body_en' => "Q: How do I buy a course?\nA: Login as a student, add courses to cart, then checkout with Stripe or Paymob.\n\nQ: How do instructors get paid?\nA: Earnings appear after paid orders, then request a payout from the instructor panel.",
                'body_ar' => "س: كيف أشتري كورس؟\nج: ادخل كطالب، أضف للسلة، ثم ادفع عبر Stripe أو Paymob.\n\nس: كيف يستلم المدرّس أرباحه؟\nج: تظهر الأرباح بعد الدفع الناجح، ثم يطلب سحبًا من لوحة المدرّس.",
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $pageData['slug']],
                ['is_published' => true]
            );
            $page->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => $pageData['en'],
                'body' => $pageData['body_en'],
            ]);
            $page->translations()->updateOrCreate(['locale' => 'ar'], [
                'title' => $pageData['ar'],
                'body' => $pageData['body_ar'],
            ]);
        }
    }
}
