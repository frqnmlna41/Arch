<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * app/Http/Kernel.php
 *
 * Snippet registrasi middleware Spatie Laravel Permission.
 *
 * Tambahkan ke $middlewareAliases (Laravel 10/11)
 * atau $routeMiddleware (Laravel 9).
 *
 * ─────────────────────────────────────────────────────────────────
 * CATATAN LARAVEL 10/11:
 * Di Laravel 10+, route middleware didaftarkan di $middlewareAliases.
 * Di Laravel 11 (dengan bootstrap/app.php baru), gunakan:
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->alias([...]);
 *   })
 * ─────────────────────────────────────────────────────────────────
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * ══════════════════════════════════════════════════════════════
     * TAMBAHKAN INI: Spatie Laravel Permission Middlewares
     * ══════════════════════════════════════════════════════════════
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive'     => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed'           => \App\Http\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ── Spatie Laravel Permission ──────────────────────────────
        'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ];
}

/*
|──────────────────────────────────────────────────────────────────
| LARAVEL 11 – bootstrap/app.php (cara alternatif)
|──────────────────────────────────────────────────────────────────
|
| Di Laravel 11, Kernel.php digantikan oleh bootstrap/app.php.
| Daftarkan middleware Spatie seperti ini:
|
| return Application::configure(basePath: dirname(__DIR__))
|     ->withRouting(
|         web: __DIR__.'/../routes/web.php',
|         commands: __DIR__.'/../routes/console.php',
|         health: '/up',
|     )
|     ->withMiddleware(function (Middleware $middleware) {
|
|         $middleware->alias([
|             'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
|             'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
|             'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
|         ]);
|
|     })
|     ->withExceptions(function (Exceptions $exceptions) {
|         //
|     })->create();
|
*/
