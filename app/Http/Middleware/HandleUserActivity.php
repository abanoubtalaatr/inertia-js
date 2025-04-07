<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class HandleUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user() ?? null;

        if ($user) {
            try {
                if (! $user->last_login_at ||
                    Carbon::parse($user->last_login_at)->diffInMinutes(now()) > 5) {

                    $user->last_login_at = now();
                    $user->save();
                }

                session(['last_activity' => now()]);
            } catch (\Exception $e) {
                \Log::error('Error updating last login: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
