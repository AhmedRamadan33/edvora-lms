<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $request->user()->instructorProfile;

        return view('instructor.pending', compact('profile'));
    }
}
