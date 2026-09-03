<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            \App\Http\Middleware\SignatureMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // 1. Validation Exception (422)
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => $e->validator->errors()->first() ?: 'Data yang dikirim tidak valid.',
                        'errors'  => $e->errors(),
                    ], 422);
                }

                // 2. Authentication Exception (401)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => 'Sesi login telah berakhir atau tidak valid. Silakan login kembali.',
                    ], 401);
                }

                // 3. Authorization / Forbidden (403)
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => $e->getMessage() ?: 'Anda tidak memiliki hak akses untuk tindakan ini.',
                    ], 403);
                }

                // 4. Not Found (404)
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => 'Data atau rute yang diminta tidak ditemukan.',
                    ], 404);
                }

                // 5. Method Not Allowed (405)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'status'  => 'error',
                        'message' => 'Metode HTTP tidak diizinkan untuk rute ini.',
                    ], 405);
                }

                // 6. Generic Server Error (500) - Log details internally, return clean message to client
                \Illuminate\Support\Facades\Log::error('API Exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'url'       => $request->fullUrl(),
                    'user_id'   => $request->user()?->id,
                ]);

                $statusCode = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                    ? $e->getStatusCode()
                    : 500;

                $message = ($statusCode >= 500 && !config('app.debug'))
                    ? 'Terjadi kesalahan pada sistem server. Silakan hubungi admin atau coba beberapa saat lagi.'
                    : ($e->getMessage() ?: 'Terjadi kesalahan pada server.');

                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => $message,
                ], $statusCode);
            }
        });
    })->create();
