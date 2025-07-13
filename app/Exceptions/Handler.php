<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Always return JSON for API requests or requests expecting JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            // Validation errors
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }
            // Authentication errors
            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            // Authorization errors
            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'Forbidden.'
                ], 403);
            }
            // Other errors
            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'message' => $exception->getMessage() ?: 'Server Error',
            ], $status);
        }
        return parent::render($request, $exception);
    }
} 