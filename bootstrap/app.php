<?php

use App\Exceptions\Contracts\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(ApiException::class);

        $exceptions->render(function (ApiException $e, Request $request) {
            return response()->json(
                ['message' => $e->getUserMessage()],
                $e->getStatusCode(),
            );
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof HttpExceptionInterface
                || $e instanceof ValidationException
                || $e instanceof AuthenticationException) {
                return null;
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ['message' => 'Internal server error'],
                    500,
                );
            }

            return null;
        });
    })->create();