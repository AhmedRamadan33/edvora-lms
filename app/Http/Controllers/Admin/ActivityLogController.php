<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->where(function ($logQuery) use ($term) {
                    $logQuery->where('action', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.activity.index', compact('logs'));
    }
}
