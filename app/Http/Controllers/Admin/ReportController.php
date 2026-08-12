<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(): View
    {
        $salesByDay = Order::query()
            ->where('status', 'paid')
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderByDesc('day')
            ->take(14)
            ->get();

        $topCourses = OrderItem::query()
            ->select('course_id', DB::raw('COUNT(*) as sales'), DB::raw('SUM(price) as revenue'))
            ->groupBy('course_id')
            ->orderByDesc('sales')
            ->with('course.translations')
            ->take(10)
            ->get();

        $platformCommission = OrderItem::query()->sum('platform_earning');
        $newStudents = User::role('student')->where('created_at', '>=', now()->subDays(30))->count();
        $publishedCourses = Course::query()->where('status', 'published')->count();

        return view('admin.reports.index', compact(
            'salesByDay', 'topCourses', 'platformCommission', 'newStudents', 'publishedCourses'
        ));
    }
}
