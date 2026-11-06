<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::validationError(
                    errors: $e->errors(),
                    message: $e->getMessage() ?: 'The given data was invalid.',
                );
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::unauthorized();
            }

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return ApiResponse::forbidden($e->getMessage() ?: 'This action is unauthorized.');
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return ApiResponse::notFound();
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponse::methodNotAllowed();
            }

            if ($e instanceof PostTooLargeException) {
                return ApiResponse::payloadTooLarge();
            }

            if ($e instanceof UnsupportedMediaTypeHttpException) {
                return ApiResponse::unsupportedMediaType();
            }

            if ($e instanceof ThrottleRequestsException) {
                return ApiResponse::tooManyRequests();
            }

            if ($e instanceof HttpExceptionInterface) {
                return ApiResponse::error(
                    message: $e->getMessage() ?: 'HTTP error occurred.',
                    status: $e->getStatusCode(),
                );
            }

            return ApiResponse::serverError();
        });
    })->create();
