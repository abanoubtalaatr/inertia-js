<?php

// app/Http/Controllers/Auth/PhoneController.php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\UpdatePhoneRequest;
use App\Http\Requests\Api\Auth\VerifyPhoneRequest;
use App\Models\PhoneVerification;

class PhoneController extends Controller
{
    use ApiResponse;

    public function update(UpdatePhoneRequest $request)
    {
        $user = $request->user();
        // $otp = sprintf('%04d', mt_rand(1, 9999)); // 6-digit OTP
        $otp = 1234;
        PhoneVerification::create([
            'user_id' => $user->id,
            'phone_number' => $request->phone_number,
            'code' => $otp,
        ]);

        return $this->success([], 'OTP sent to new phone number');
    }

    public function verify(VerifyPhoneRequest $request)
    {
        $user = $request->user();
        $verification = PhoneVerification::where('user_id', $user->id)
            ->where('code', $request->otp)
            ->first();

        if (! $verification) {
            return $this->error('Invalid or expired OTP');
        }

        $user->phone = $verification->phone_number;

        $user->save();

        $verification->delete();

        $data['phone_number'] = $user->phone;

        return $this->success($data, 'Phone number updated and verified successfully');
    }
}
