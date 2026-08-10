<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
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
    ->withBroadcasting(__DIR__.'/../routes/channels.php', [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (
                !$request->is('api/*')
                || $exception instanceof AuthenticationException
                || $exception instanceof ValidationException
                || $exception instanceof HttpExceptionInterface
            ) {
                return null;
            }

            $lang = $request->header('Accept-Language', 'ar');
            $message = str_starts_with((string) $lang, 'en')
                ? 'The server could not complete the request. Please try again.'
                : 'تعذر على الخادم إكمال الطلب. يرجى المحاولة مرة أخرى.';

            return response()->json(['message' => $message], 500);
        });
    })->create();
