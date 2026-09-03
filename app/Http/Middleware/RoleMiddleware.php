<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Mendukung single role (role:admin) maupun multi-role (role:admin,wadek).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $allowedRoles = [];
        foreach ($roles as $roleArg) {
            foreach (explode(',', $roleArg) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (!$user->hasAnyRole($allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role yang diperlukan: ' . implode(', ', $allowedRoles),
            ], 403);
        }

        return $next($request);
    }
}
