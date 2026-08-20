<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreLiveClassRequest;
use App\Http\Requests\Instructor\UpdateLiveClassRequest;
use App\Models\Course;
use App\Models\LiveClass;
use App\Services\LiveClassService;
use Illuminate\Http\RedirectResponse;

class LiveClassController extends Controller
{
    public function store(StoreLiveClassRequest $request, Course $course, LiveClassService $liveClasses): RedirectResponse
    {
        try {
            $liveClasses->schedule($course, auth()->user(), $request->validated());
        } catch (\Throwable $e) {
            return back()->with('error', __('Unable to schedule the live class: :message', ['message' => $e->getMessage()]));
        }

        return back()->with('success', __('Live class scheduled.'));
    }

    public function update(UpdateLiveClassRequest $request, LiveClass $liveClass, LiveClassService $liveClasses): RedirectResponse
    {
        try {
            $liveClasses->reschedule($liveClass, $request->validated());
        } catch (\Throwable $e) {
            return back()->with('error', __('Unable to update the live class: :message', ['message' => $e->getMessage()]));
        }

        return back()->with('success', __('Live class updated.'));
    }

    public function destroy(LiveClass $liveClass, LiveClassService $liveClasses): RedirectResponse
    {
        $this->authorizeLiveClass($liveClass);

        $liveClasses->delete($liveClass);

        return back()->with('success', __('Live class deleted.'));
    }

    protected function authorizeLiveClass(LiveClass $liveClass): void
    {
        abort_unless($liveClass->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
    }
}
