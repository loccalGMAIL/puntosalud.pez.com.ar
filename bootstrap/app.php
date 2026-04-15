<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('whatsapp:send-reminders')->everyFifteenMinutes();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
            'check.center.active' => \App\Http\Middleware\CheckCenterActive::class,
            'module' => \App\Http\Middleware\EnsureModuleAccess::class,
        ]);

        $middleware->web([
            \App\Http\Middleware\CheckUserActive::class,
            \App\Http\Middleware\CheckCenterActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo de error 419 - CSRF Token Expired
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Si es una petición AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión ha expirado. Por favor, recarga la página e inicia sesión nuevamente.',
                    'redirect' => route('login')
                ], 419);
            }

            // Si es una petición web normal
            return redirect()->route('login')
                ->withErrors(['session' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.']);
        });
    })->create();
