<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\RequestPayoutRequest;
use App\Services\AdminCatalogService;
use App\Services\InstructorProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EarningController extends Controller
{
    public function index(InstructorProfileService $profiles): View
    {
        $summary = $profiles->earningsSummary(auth()->user());

        return view('instructor.earnings.index', $summary);
    }

    public function requestPayout(RequestPayoutRequest $request, AdminCatalogService $catalog): RedirectResponse
    {
        $result = $catalog->requestPayout(auth()->user(), $request->validated());

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
