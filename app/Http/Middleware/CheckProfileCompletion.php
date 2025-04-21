<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && (empty($user->first_name) || empty($user->last_name) || empty($user->national_code))) {
            return response()->apiResult(
                ['redirect_to_profile' => true],
                409,
                ['لطفا اطلاعات پروفایل خود را تکمیل کنید.']
            );
        }

        return $next($request);
    }
}
