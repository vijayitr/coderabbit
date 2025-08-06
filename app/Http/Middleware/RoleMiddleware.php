<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  One or more roles that are allowed to access the route
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check if the user has any of the allowed roles
            if (Auth::user()->hasAnyRole($roles)) {
                return $next($request);
            }
        }

        // If the user is not authorized, abort with a 403 error
        abort(403, 'Unauthorized access');
    }
}
