<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            dd('here');
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
