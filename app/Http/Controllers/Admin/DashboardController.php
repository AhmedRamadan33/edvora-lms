<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'courses' => Course::query()->count(),
            'published' => Course::query()->where('status', 'published')->count(),
            'pending' => Course::query()->where('status', 'pending_review')->count(),
            'revenue' => Order::query()->where('status', 'paid')->sum('total'),
            'orders' => Order::query()->where('status', 'paid')->count(),
        ];

        $recentOrders = Order::query()->with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
