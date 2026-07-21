<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if (auth('admin')->check()) {
                return '/admin/dashboard';
            } elseif (auth('bendahara')->check()) {
                return '/bendahara/dashboard';
            } elseif (auth('kepala_desa')->check()) {
                return '/kepala-desa/dashboard';
            } elseif (auth('kaur_umum')->check()) {
                return '/kaur-umum/dashboard';
            }
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
