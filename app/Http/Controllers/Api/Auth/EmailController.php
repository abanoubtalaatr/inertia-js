<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\EmailVerificationRequest;
use App\Http\Requests\Api\Auth\VerifyEmailRequest;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    use ApiResponse;

    public function update(EmailVerificationRequest $request)
    {
        $user = $request->user();
        // $otp = sprintf("%04d", mt_rand(1, 9999)); // 4-digit OTP
        $otp = 1234;
        // Store the verification attempt
        $verification = EmailVerification::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'token' => $otp, // Using OTP as token
        ]);

        // Send OTP via email
        Mail::raw("Your verification OTP is: {$otp}", function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Email Verification OTP');
        });

        return $this->success([], 'OTP sent to new email address');
    }

    public function verify(VerifyEmailRequest $request)
    {
        $user = $request->user();
        $verification = EmailVerification::where('user_id', $user->id)
            ->where('token', $request->otp)
            ->first();

        if (! $verification) {
            return $this->error('Invalid or expired OTP');
        }

        $user->email = $verification->email;
        $user->email_verified_at = now();
        $user->save();

        $verification->delete();

        return $this->success([], 'Email updated and verified successfully');
    }
}
