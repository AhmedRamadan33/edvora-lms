<?php

namespace App\Http\Controllers;

use App\Repositories\TestimonialRepository;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(TestimonialRepository $testimonials): View
    {
        $testimonials = $testimonials->publishedAll();

        return view('testimonials.index', compact('testimonials'));
    }
}
