<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountTypeSelected
{
    /**
     * New accounts created via social login have no role yet - send them to
     * pick student/instructor before they can reach any protected page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->roles()->doesntExist() && ! $request->routeIs('social.choose-type')) {
            return redirect()->route('social.choose-type');
        }

        return $next($request);
    }
}
