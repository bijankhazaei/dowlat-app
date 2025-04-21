<?php

use App\Http\Middleware\AuthDefaultGuard;
use App\Http\Middleware\CheckProfileCompletion;
use App\Http\Middleware\EnsureApiResult;
use App\Http\Middleware\ZibalCallbackAuthentication;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('global', [
            TrustHosts::class,
            TrustProxies::class,
            ValidatePostSize::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->group('api', [
            'throttle:api',
            SubstituteBindings::class,
            EnsureApiResult::class,
            'auth.optional:api'
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'auth.optional' => AuthDefaultGuard::class,
            'auth.zibal' => ZibalCallbackAuthentication::class,
            'cache.headers' => SetCacheHeaders::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'profile.completed' => CheckProfileCompletion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (config('app.env') === 'production') {
            Integration::handles($exceptions);
        }
    })->create();
