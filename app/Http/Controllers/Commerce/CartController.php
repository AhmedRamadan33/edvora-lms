<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = CartItem::query()
            ->where('user_id', auth()->id())
            ->with('course.translations')
            ->get();

        $subtotal = $items->sum(fn ($item) => (float) $item->course->price);

        return view('commerce.cart', compact('items', 'subtotal'));
    }

    public function store(Course $course, CartService $cart): RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        if (auth()->user()->isEnrolledIn($course->id)) {
            return back()->with('error', __('Already enrolled.'));
        }

        $cart->add(auth()->user(), $course);

        return redirect()->route('cart.index')->with('success', __('Added to cart.'));
    }

    public function destroy(Course $course, CartService $cart): RedirectResponse
    {
        $cart->remove(auth()->user(), $course);

        return back()->with('success', __('Removed from cart.'));
    }
}
