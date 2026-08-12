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
                'body_en' => "Edvora is a marketplace LMS where instructors sell courses and the platform takes a commission.\n\nLearners get bilingual experiences, secure video lessons, progress tracking, and certificates.",
                'body_ar' => "إدفورا منصة تعليمية بنظام الماركت بليس: المدرّس يبيع الكورسات، والمنصة تأخذ عمولة.\n\nيحصل المتعلمون على تجربة ثنائية اللغة ودروس فيديو آمنة وتتبع تقدم وشهادات.",
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
