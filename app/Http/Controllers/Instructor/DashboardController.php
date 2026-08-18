<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\InstructorEarning;
use App\Services\ExamGradingService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ExamGradingService $grading): View
    {
        $instructorId = auth()->id();

        $stats = [
            'courses' => Course::query()->where('instructor_id', $instructorId)->count(),
            'published' => Course::query()->where('instructor_id', $instructorId)->where('status', 'published')->count(),
            'students' => Course::query()->where('instructor_id', $instructorId)->sum('students_count'),
            'earnings' => InstructorEarning::query()->where('instructor_id', $instructorId)->sum('amount'),
            'available' => InstructorEarning::query()->where('instructor_id', $instructorId)->where('status', 'available')->sum('amount'),
            'pending_reviews' => $grading->pendingReviewCount(auth()->user()),
        ];

        $courses = Course::query()
            ->where('instructor_id', $instructorId)
            ->with('translations')
            ->latest()
            ->take(5)
            ->get();

        $recentAttempts = $grading->recentActivity(auth()->user());

        return view('instructor.dashboard', compact('stats', 'courses', 'recentAttempts'));
    }
}
