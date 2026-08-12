<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            'instructor@edvora.test' => [
                'headline' => 'Laravel & Marketplace Expert',
                'about' => 'I help developers ship real LMS products with secure video, payments, and clean architecture.',
                'website' => 'https://example.com/sara',
                'status' => 'approved',
                'commission_rate' => 15,
                'days' => 40,
            ],
            'ahmed@edvora.test' => [
                'headline' => 'UI/UX & Frontend Mentor',
                'about' => 'تصميم واجهات عربية وتجربة مستخدم للمنصات التعليمية والمنتجات الرقمية.',
                'website' => 'https://example.com/ahmed',
                'status' => 'approved',
                'commission_rate' => 18,
                'days' => 30,
            ],
            'nour@edvora.test' => [
                'headline' => 'Data Science & AI Practitioner',
                'about' => 'Applied ML, analytics, and AI product workflows for real business problems.',
                'website' => 'https://example.com/nour',
                'status' => 'approved',
                'commission_rate' => 17,
                'days' => 25,
            ],
            'layla@edvora.test' => [
                'headline' => 'Growth Marketing & Business Coach',
                'about' => 'أساعد الفرق على بناء قنوات نمو مستدامة واستراتيجيات تسعير واضحة.',
                'website' => 'https://example.com/layla',
                'status' => 'approved',
                'commission_rate' => 16,
                'days' => 20,
            ],
            'karim@edvora.test' => [
                'headline' => 'Cloud, DevOps & Security Engineer',
                'about' => 'CI/CD, Kubernetes, and practical cybersecurity for shipping teams.',
                'website' => 'https://example.com/karim',
                'status' => 'approved',
                'commission_rate' => 19,
                'days' => 18,
            ],
            'pending@edvora.test' => [
                'headline' => 'Waiting for approval',
                'about' => 'New instructor application for admin review in the demo.',
                'website' => null,
                'status' => 'pending',
                'commission_rate' => null,
                'days' => null,
            ],
        ];

        foreach ($profiles as $email => $data) {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                continue;
            }

            InstructorProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'headline' => $data['headline'],
                    'about' => $data['about'],
                    'website' => $data['website'],
                    'status' => $data['status'],
                    'commission_rate' => $data['commission_rate'],
                    'approved_at' => $data['status'] === 'approved' ? now()->subDays($data['days']) : null,
                ]
            );
        }
    }
}
