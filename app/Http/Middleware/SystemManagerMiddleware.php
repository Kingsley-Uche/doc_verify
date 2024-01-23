<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SystemManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        
        if (!Auth::check()) {

            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Check if the user is a system admin
        if (Auth::user()->is_system_admin) {

            return $next($request);
        }

        // If not a system admin, return the same unauthenticated response
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }
}
