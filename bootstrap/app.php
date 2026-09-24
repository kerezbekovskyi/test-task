<?php

use App\Exceptions\DynamicApiException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'timestamp' => now()->toJSON(),
                'status' => 400,
                'error' => 'Bad Request',
                'message' => $exception->validator->errors()->first(),
                'path' => '/'.ltrim($request->path(), '/'),
            ], 400);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof DynamicApiException) {
                return null;
            }

            return response()->json([
                'timestamp' => now()->toJSON(),
                'status' => 500,
                'error' => 'Internal Server Error',
                'message' => config('app.debug') ? $exception->getMessage() : 'Internal server error.',
                'path' => '/'.ltrim($request->path(), '/'),
            ], 500);
        });
    })->create();
