<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccountType
{
    protected $accountType;

    public function __construct($accountType)
    {
        $this->accountType = $accountType;
    }

    public function handle(Request $request, Closure $next, $type)
    {
        if (auth()->check() && auth()->user()->type !== $type) {
            return response()->json(['message' => 'Unauthorized access for this account type.'], 403);
        }

        return $next($request);
    }
}
