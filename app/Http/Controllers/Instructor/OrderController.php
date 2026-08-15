<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\FilterInstructorOrdersRequest;
use App\Services\InstructorOrderService;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(FilterInstructorOrdersRequest $request, InstructorOrderService $orders): View
    {
        $orders = $orders->paginateForInstructor($request->user(), $request->validated());

        return view('instructor.orders.index', compact('orders'));
    }
}
