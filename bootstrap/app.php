<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'isAdmin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            ThrottleRequestsException $e,
            Request $request
        ): Response {
            // Abaikan header bawaan, pakai fixed 60 detik (atau 90 kalau mau)
            $retryAfter = 60;

            return back()
                ->withErrors([
                    'email' => "Terlalu banyak percobaan login. Silakan coba lagi."
                ])
                ->with('retry_after', $retryAfter)
                ->withInput($request->except('password'));
        });
    })->create();
