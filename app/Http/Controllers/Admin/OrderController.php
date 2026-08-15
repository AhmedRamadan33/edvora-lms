<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterOrdersRequest;
use App\Services\AdminOrderService;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(FilterOrdersRequest $request, AdminOrderService $orders): View
    {
        $orders = $orders->paginateCompleted($request->validated());

        return view('admin.orders.index', compact('orders'));
    }
}
