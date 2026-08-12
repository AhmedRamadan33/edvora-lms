<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\UpdateInstructorProfileRequest;
use App\Services\InstructorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(InstructorProfileService $profiles): View
    {
        $profile = $profiles->getOrCreate(auth()->user());

        return view('instructor.profile.edit', compact('profile'));
    }

    public function update(UpdateInstructorProfileRequest $request, InstructorProfileService $profiles): RedirectResponse
    {
        $profiles->update(auth()->user(), $request->validated());

        return back()->with('success', __('Instructor profile saved.'));
    }
}
