<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Models\Coupon;
use App\Services\AdminCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request, AdminCatalogService $catalog): View
    {
        $coupons = $catalog->paginateCoupons(search: $request->string('search')->trim()->toString() ?: null);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(StoreCouponRequest $request, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->createCoupon($request->validated());

        return back()->with('success', __('Coupon created.'));
    }

    public function destroy(Coupon $coupon, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->deleteCoupon($coupon);

        return back()->with('success', __('Coupon deleted.'));
    }
}
