<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActiveAccount
{
    public function handle(Request $request, Closure $next)
    {

        $userProvider = auth('provider')->user();
        $userHotel = auth('hotel')->user();
        if (auth('provider')->check() && ! $userProvider->is_active) {

            return response()->json(['message' => __('messages.account_not_active')], 403);
        }

        if (auth('hotel')->check() && ! $userHotel->is_active) {

            return response()->json(['message' => __('messages.account_not_active')], 403);
        }

        return $next($request);
    }
}
