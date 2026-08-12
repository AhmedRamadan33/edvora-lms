<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $enrollments = Enrollment::query()
            ->where('user_id', auth()->id())
            ->with('course.translations')
            ->latest()
            ->get();

        return view('student.dashboard', compact('enrollments'));
    }
}
