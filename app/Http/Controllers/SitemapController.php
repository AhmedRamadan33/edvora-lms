<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = collect();

            $urls->push(['loc' => route('home'), 'lastmod' => now(), 'priority' => '1.0']);
            $urls->push(['loc' => route('courses.index'), 'lastmod' => now(), 'priority' => '0.9']);
            $urls->push(['loc' => route('testimonials.index'), 'lastmod' => now(), 'priority' => '0.5']);
            $urls->push(['loc' => route('contact.show'), 'lastmod' => now(), 'priority' => '0.5']);

            Course::query()->published()->select(['slug', 'updated_at'])->get()->each(function (Course $course) use ($urls) {
                $urls->push([
                    'loc' => route('courses.show', $course->slug),
                    'lastmod' => $course->updated_at,
                    'priority' => '0.8',
                ]);
            });

            Page::query()->where('is_published', true)->get()->each(function (Page $page) use ($urls) {
                $urls->push([
                    'loc' => route('pages.show', $page->slug),
                    'lastmod' => $page->updated_at,
                    'priority' => '0.4',
                ]);
            });

            User::query()
                ->whereHas('instructorProfile', fn ($q) => $q->where('status', 'approved'))
                ->whereHas('courses', fn ($q) => $q->published())
                ->select(['id', 'updated_at'])
                ->get()
                ->each(function (User $instructor) use ($urls) {
                    $urls->push([
                        'loc' => route('instructors.show', $instructor->id),
                        'lastmod' => $instructor->updated_at,
                        'priority' => '0.4',
                    ]);
                });

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
