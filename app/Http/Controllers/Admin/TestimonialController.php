<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(TestimonialService $testimonials): View
    {
        $testimonials = $testimonials->paginateForAdmin();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function store(StoreTestimonialRequest $request, TestimonialService $testimonials): RedirectResponse
    {
        $testimonials->create(
            $request->validated(),
            $request->boolean('is_published', true),
            $request->boolean('show_on_home', true)
        );

        return back()->with('success', __('Testimonial created.'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial, TestimonialService $testimonials): RedirectResponse
    {
        $testimonials->update(
            $testimonial,
            $request->validated(),
            $request->boolean('is_published'),
            $request->boolean('show_on_home')
        );

        return back()->with('success', __('Testimonial updated.'));
    }

    public function destroy(Testimonial $testimonial, TestimonialService $testimonials): RedirectResponse
    {
        $testimonials->delete($testimonial);

        return back()->with('success', __('Testimonial deleted.'));
    }
}
