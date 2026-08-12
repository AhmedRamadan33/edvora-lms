<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(User $user): View
    {
        abort_unless($user->hasRole('instructor'), 404);

        $user->load('instructorProfile');
        $courses = $user->courses()->published()->with('translations')->latest('published_at')->get();

        return view('instructor.public', compact('user', 'courses'));
    }
}
