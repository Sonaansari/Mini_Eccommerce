<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    /**
     * Render exceptions into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Return JSON for API routes
        if ($request->is('api/*')) {

            // Unauthorized (not logged in)
            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated. Please login.',
                    'data' => new \stdClass()
                ], 401);
            }

            // Forbidden (no permission)
            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied: You are not authorized for this action.',
                    'data' => new \stdClass()
                ], 403);
            }

            // Not Found (model or route)
            if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Resource not found.',
                    'data' => new \stdClass()
                ], 404);
            }

            // Other HTTP Exceptions
            if ($exception instanceof HttpException) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage() ?: 'HTTP Error',
                    'data' => new \stdClass()
                ], $exception->getStatusCode());
            }

            // Generic Exception (500)
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $exception->getMessage(),
                'data' => new \stdClass()
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
