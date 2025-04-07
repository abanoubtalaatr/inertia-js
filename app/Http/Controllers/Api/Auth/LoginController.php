<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelProfileResource;
use App\Http\Resources\ProviderProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'in:true,false,1,0',
        ]);

        $guards = ['provider', 'hotel'];
        $user = null;
        $token = null;
        $activeGuard = null;

        foreach ($guards as $guard) {
            $rememberMe = filter_var($request->input('remember_me'), FILTER_VALIDATE_BOOLEAN);

            if ($rememberMe) {
                Auth::guard($guard)->setTTL(43200); // 30 يومًا
            }

            if ($token = Auth::guard($guard)->attempt($request->only('email', 'password'))) {
                $user = Auth::guard($guard)->user();
                $activeGuard = $guard;
                break;
            }
        }

        if (! $user) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        if (is_null($user->email_verified_at)) {
            Auth::guard($activeGuard)->logout();

            return response()->json(['message' => __('messages.email_not_verified')], 403);
        }

        if ($user->is_active == 0) {
            Auth::guard($activeGuard)->logout();

            return response()->json(['message' => __('messages.account_not_active')], 400);
        }

        return response()->json([
            'message' => __('auth.login_success'),
            'data' => $user->type == 'hotel'
                ? new HotelProfileResource($user)
                : new ProviderProfileResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $guard = auth('provider')->check() ? 'provider' : (auth('hotel')->check() ? 'hotel' : null);

        if (! $guard) {
            return response()->json(['message' => __('auth.invalid_user_type')], 400);
        }

        if (Auth::guard($guard)->check()) {
            Auth::guard($guard)->logout();

            return response()->json(['message' => __('messages.logout_success')], 200);
        }

        return response()->json(['message' => __('auth.unauthenticated')], 401);
    }
}
