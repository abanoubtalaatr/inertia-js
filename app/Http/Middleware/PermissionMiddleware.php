<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        if (! $request->user()->hasPermission($permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have the required permission.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
