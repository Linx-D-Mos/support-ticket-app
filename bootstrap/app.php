<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    // bootstrap/app.php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Excluimos la ruta de auth de la protección CSRF porque usamos Tokens
        $middleware->validateCsrfTokens(except: [
            'broadcasting/auth'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            //Solo intervenimos si es una petición de Api o espera JSON explicitly
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'true',
                    'message' => 'El recurso solicitado no existe',
                    'code' => 404
                ], 404);
            }
            //Si es web normal, dejamos que laravel muestre su vista 404 por defecto.
        });
    })->create();
