<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateContractStatus
{
    public function handle(Request $request, Closure $next)
    {
        $contract = $request->route('contract');

        if ($contract->status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'This contract has expired',
            ], 403);
        }

        return $next($request);
    }
}
