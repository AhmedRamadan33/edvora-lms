<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreAnnouncementRequest;
use App\Services\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request, AnnouncementService $announcements): View
    {
        return view('instructor.announcements.index', [
            'announcements' => $announcements->paginateForSender(auth()->user(), 15, $request->string('search')->toString() ?: null),
            'students' => $announcements->studentsForInstructor(auth()->user()),
        ]);
    }

    public function store(StoreAnnouncementRequest $request, AnnouncementService $announcements): RedirectResponse
    {
        $announcement = $announcements->sendAsInstructor(auth()->user(), $request->validated());

        return redirect()
            ->route('instructor.announcements.index')
            ->with('success', __('Announcement sent to :count student(s).', ['count' => $announcement->recipients_count]));
    }
}
