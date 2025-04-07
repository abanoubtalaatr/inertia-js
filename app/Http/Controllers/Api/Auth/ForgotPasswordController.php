<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\Account;
use App\Services\OTPService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(ForgotPasswordRequest $request)
    {
        if (Account::where('email', $request->email)->exists()) {
            $user = Account::where('email', $request->email)->first();
            if ($user->is_active == 0) {
                return response()->json(['message' => __('messages.account_not_active')], 400);
            }

        }
        $otp = $this->otpService->generateOtp($request->email, 'password_reset');

        dispatch(new \App\Jobs\SendOtpEmail($request->email, $otp));

        return response()->json(['message' => __('messages.otp_sent'), 'otp' => $otp]);
    }

    public function verifyOtp(VerifyEmailRequest $request)
    {
        if (! $this->otpService->verifyOtp($request->email, $request->otp, 'password_reset')) {
            return response()->json(['message' => __('messages.invalid_or_expired_otp')], 400);
        }

        $cacheKey = "password_reset_verified_{$request->email}";
        Cache::put($cacheKey, true, now()->addMinutes(15));

        return response()->json(['message' => __('messages.otp_verified')]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $cacheKey = "password_reset_verified_{$request->email}";
        if (! Cache::get($cacheKey)) {
            return response()->json(['message' => __('messages.otp_not_verified')], 400);
        }

        $account = Account::where('email', $request->email)->first();
        $account->password = Hash::make($request->password);
        $account->save();

        Cache::forget($cacheKey);

        return response()->json(['message' => __('messages.password_reset')]);
    }
}
