<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ClearOldLogs;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->appendToGroup('auth.custom', [\App\Http\Middleware\CustomAuthMiddleware::class]);
        $middleware->appendToGroup('auth.jwt', [\Tymon\JWTAuth\Http\Middleware\Authenticate::class]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(ClearOldLogs::class)->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();