<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        if (! in_array($request->user()->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have the required role.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
