<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class OTP
{
    public static function generateOtp($length = 6)
    {
        $otp = 123456;

        return $otp;
        // return Str::random($length);
    }
}
