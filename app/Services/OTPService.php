<?php

namespace App\Services;

use App\Jobs\SendOtpEmail;
use App\Models\Account;
use App\Models\Otp;
use Carbon\Carbon;

class OTPService
{
    public function generateOtp($email, $type)
    {
        $otp = 1111; //  الكود
        $expiresAt = Carbon::now()->addMinutes(10);

        Otp::updateOrCreate(
            ['email' => $email, 'type' => $type],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        return $otp;
    }

    public function verifyOtp($email, $otp, $type)
    {
        $record = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('expires_at', '>=', Carbon::now())
            ->first();

        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }

    /**
     * Resend OTP.
     */
    public function resendOtp($email, $userModel = null, $type = 'email_verification')
    {
        $userModel = $userModel ?? Account::class;

        $user = $userModel::where('email', $email)->first();

        if (! $user) {
            return response()->json(['message' => __('messages.account_not_found')], 404);
        }

        if ($user->is_active == 0) {
            return response()->json(['message' => __('messages.account_not_active')], 400);
        }

        $otp = $this->generateOtp($email, $type);

        dispatch(new SendOtpEmail($email, $otp));

        return response()->json(['message' => __('messages.otp_sent'), 'otp' => $otp]);
    }
}
