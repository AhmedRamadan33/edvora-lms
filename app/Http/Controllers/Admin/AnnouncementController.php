<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Services\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request, AnnouncementService $announcements): View
    {
        return view('admin.announcements.index', [
            'announcements' => $announcements->paginateForSender(auth()->user(), 15, $request->string('search')->toString() ?: null),
            'students' => $announcements->studentsForAdmin(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, AnnouncementService $announcements): RedirectResponse
    {
        $announcement = $announcements->sendAsAdmin(auth()->user(), $request->validated());

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', __('Announcement sent to :count student(s).', ['count' => $announcement->recipients_count]));
    }
}
