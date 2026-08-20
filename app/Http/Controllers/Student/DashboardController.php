<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Repositories\LiveClassRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(LiveClassRepository $liveClasses): View
    {
        $enrollments = Enrollment::query()
            ->where('user_id', auth()->id())
            ->with('course.translations')
            ->latest()
            ->get();

        $upcomingLiveClasses = $liveClasses->upcomingForStudent(auth()->id());

        return view('student.dashboard', compact('enrollments', 'upcomingLiveClasses'));
    }
}
