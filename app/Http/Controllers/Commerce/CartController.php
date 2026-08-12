<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
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

    public function store(Course $course): RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        if (auth()->user()->isEnrolledIn($course->id)) {
            return back()->with('error', __('Already enrolled.'));
        }

        CartItem::query()->firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
        ]);

        return redirect()->route('cart.index')->with('success', __('Added to cart.'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        CartItem::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->delete();

        return back()->with('success', __('Removed from cart.'));
    }
}
