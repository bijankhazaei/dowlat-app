<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthDefaultGuard
{
    public function handle(Request $request, Closure $next, $guard = 'web')
    {
        Auth::shouldUse($guard); 
        return $next($request);
    }
}
