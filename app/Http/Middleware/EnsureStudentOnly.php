<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentOnly
{
    /**
     * Blocks course enrollment (cart/checkout) for instructor and admin accounts -
     * only students may enroll in courses, regardless of what other roles a user holds.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasRole('admin') || $user->hasRole('instructor'))) {
            return redirect()->back(fallback: route('home'))->with('error', __('Only student accounts can enroll in courses.'));
        }

        return $next($request);
    }
}
