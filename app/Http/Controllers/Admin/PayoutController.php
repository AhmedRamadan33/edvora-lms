<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPayoutRequest;
use App\Models\PayoutRequest;
use App\Services\AdminCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(Request $request): View
    {
        $payouts = PayoutRequest::query()
            ->with('instructor')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->whereHas('instructor', fn ($instructorQuery) => $instructorQuery
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"))
                    ->orWhere('method', 'like', "%{$term}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payouts.index', compact('payouts'));
    }

    public function approve(Request $request, PayoutRequest $payout, AdminCatalogService $catalog): RedirectResponse
    {
        $result = $catalog->approvePayout($payout, $request->input('admin_note'));

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reject(RejectPayoutRequest $request, PayoutRequest $payout, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->rejectPayout($payout, $request->validated('admin_note'));

        return back()->with('success', __('Payout rejected.'));
    }
}
