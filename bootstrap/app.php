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
        $middleware->append(\Illuminate\Routing\Middleware\ThrottleRequests::class.':global');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'درخواست‌های شما بیش از حد مجاز است. لطفاً چند لحظه صبر کنید.',
                ], 429);
            }

            return back()->withErrors([
                'throttle' => 'درخواست‌های شما بیش از حد مجاز است. لطفاً چند لحظه صبر کنید.',
            ]);
        });
    })->create();
