<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\OTP;
use App\Http\Controllers\Controller;
use App\Jobs\SendOtpEmail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the email
        $request->validate([
            'email' => 'required|email|exists:users,email', // Check if the email exists in the database
        ], [
            'email.required' => trans('validation.required', ['attribute' => trans('auth.email')]),
            'email.email' => trans('validation.email', ['attribute' => trans('auth.email')]),
            'email.exists' => trans('validation.exists', ['attribute' => trans('auth.email')]),
        ]);

        $otp = OTP::generateOtp();

        Cache::put('otp_'.$request->email, $otp, now()->addMinutes(10));

        try {
            SendOtpEmail::dispatch($request->email, $otp);

            return redirect()->route('auth.password.otp.form', ['email' => $request->email])->with('status', __('messages.otp_sent'));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP: '.$e->getMessage());

            return back()->withErrors(['email' => __('errors.otp_failed')]);
        }
    }

    public function otpForm(Request $request): Response
    {
        return Inertia::render('Auth/OtpVerification', [
            'email' => $request->query('email'),
            'status' => session('status'),
        ]);
    }

    public function resendOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $otp = OTP::generateOtp();

        Cache::put('otp_'.$request->email, $otp, now()->addMinutes(10));

        try {
            SendOtpEmail::dispatch($request->email, $otp);

            return redirect()->back()->with('status', __('messages.otp_sent')); // إعادة توجيه مع رسالة
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP: '.$e->getMessage());

            return redirect()->back()->withErrors(['email' => __('errors.otp_failed')]);
        }
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $otp = implode('', $request->input('otp'));

        $request->merge(['otp' => $otp]);
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ], [
            'email.required' => trans('validation.required', ['attribute' => trans('auth.email')]),
            'email.email' => trans('validation.email', ['attribute' => trans('auth.email')]),
            'otp.required' => trans('validation.required', ['attribute' => trans('auth.otp')]),
            'otp.numeric' => trans('validation.numeric', ['attribute' => trans('auth.otp')]),
        ]);

        $cachedOtp = Cache::get('otp_'.$request->email);
        $user = User::where('email', $request->email)->firstOrFail();

        $token = Password::createToken($user);
        if (! $cachedOtp || $cachedOtp != $request->otp) {

            return back()->withErrors(['otp' => __('messages.otp_invalid')]);
        }

        Cache::forget('otp_'.$request->email);

        return redirect()->route('password.reset', ['token' => $token,
            'email' => $user->email, ])
            ->with('status', __('messages.otp_verified'));
    }
}
