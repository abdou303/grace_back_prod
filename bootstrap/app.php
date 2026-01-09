<?php

use App\Http\Middleware\ConvertNullStrings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //Par moi le 03.04.2025
        //Par moi le 03.04.2025

        // Ajoutez-le ici pour qu'il s'applique à toutes les requêtes
        $middleware->append(ConvertNullStrings::class);
        // $middleware->append(App\Http\Middleware\CorsMiddleware::class);
        // other middleware if any...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
