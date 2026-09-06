<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorApproved
{
    /**
     * Instructor accounts (registered directly or via social login) get the
     * role immediately, but must not use the instructor panel until an admin
     * approves their InstructorProfile.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->hasRole('instructor')
            && ! $user->hasRole('admin')
            && ! $request->routeIs('instructor.pending')
            && $user->instructorProfile?->status !== 'approved'
        ) {
            return redirect()->route('instructor.pending');
        }

        return $next($request);
    }
}
