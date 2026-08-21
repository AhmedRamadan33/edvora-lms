<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateInstructorRequest;
use App\Http\Requests\Admin\RejectInstructorRequest;
use App\Models\InstructorProfile;
use App\Services\AdminCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = InstructorProfile::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->whereHas('user', fn ($userQuery) => $userQuery
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.instructors.index', compact('profiles'));
    }

    public function approve(InstructorProfile $profile, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->approveInstructor($profile);

        return back()->with('success', __('Instructor approved.'));
    }

    public function reject(RejectInstructorRequest $request, InstructorProfile $profile, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->rejectInstructor($profile, $request->validated('rejection_reason'));

        return back()->with('success', __('Instructor rejected.'));
    }

    public function create(): View
    {
        return view('admin.instructors.create');
    }

    public function store(CreateInstructorRequest $request, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->createInstructor($request->validated());

        return redirect()->route('admin.instructors.index')->with('success', __('Instructor account created. An email was sent so they can set their password.'));
    }
}
