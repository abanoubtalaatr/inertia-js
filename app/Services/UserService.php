<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\VerifyOtpRequest;
use App\Models\OtpVerification;
use App\Models\User;
use App\Notifications\OtpSent;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationRejected;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    use ApiResponse;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(Request $request)
    {
        // Handle file uploads
        $verificationDocuments = $this->uploadFiles($request, 'verification_documents', 'verification_documents');
        $logoPath = $this->uploadFile($request, 'logo', 'company_logos');
        $profilePicturePath = $this->uploadFile($request, 'profile_picture', 'profile_pictures');

        // Determine user status
        $status = in_array($request->role, ['specialist', 'company']) ? 'pending_verification' : 'pending_verification';

        // Prepare user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_picture' => $profilePicturePath,
            'preferred_language' => $request->preferred_language ?? 'ar',
            'role' => $request->role,
            'status' => $status,
            'verified' => false,
        ];

        // Add role-specific fields
        $userData = array_merge($userData, $this->getRoleSpecificData($request, $verificationDocuments, $logoPath));

        // Create user using repository
        $user = $this->userRepository->createUser($userData);

        // should send otp now
        $skipOtp = $request->has('skip_otp') && $request->skip_otp === true;

        $this->generateAndSendOtp($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,

            'message' => $status === 'pending'
                ? 'Registration successful, awaiting verification'
                : 'User registered successfully',
        ];
    }

    private function uploadFiles(Request $request, string $key, string $directory): array
    {
        return $request->hasFile($key)
            ? array_map(fn ($file) => $file->store($directory, 'public'), $request->file($key))
            : [];
    }

    private function uploadFile(Request $request, string $key, string $directory): ?string
    {
        return $request->hasFile($key)
            ? $request->file($key)->store($directory, 'public')
            : null;
    }

    private function getRoleSpecificData(Request $request, array $verificationDocuments, ?string $logoPath): array
    {
        return match ($request->role) {
            'specialist' => [
                'specialization' => $request->specialization,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
                'bio' => $request->bio,
                'company_id' => $request->company_id,
                'verification_documents' => $verificationDocuments,
                'available_days' => $request->available_days ?? [],
                'available_hours' => $request->available_hours ?? [],
            ],
            'company' => [
                'commercial_register' => $request->commercial_register,
                'tax_number' => $request->tax_number,
                'description' => $request->description,
                'logo' => $logoPath,
                'verification_documents' => $verificationDocuments,
            ],
            'user' => [
                'company_id' => $request->company_id,
                'is_active' => 0,
            ],
            default => []
        };
    }

    /**
     * Verify user account using OTP
     *
     * @param  Request  $request
     * @return array
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $otp = $request->otp;

        $user = User::where('email', $request->username)->orWhere('phone', $request->username)->first();

        if (! $user) {
            $result['message'] = 'User not found';
            $result['data'] = [];

            return $result;
        }

        $verification = OtpVerification::where('user_id', $user->id)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            $result['data'] = [];
            $result['message'] = 'Otp Expired';

            return $result;
        }

        // Mark OTP as used
        $verification->delete();

        $user->verified = true;
        $user->is_active = 1;
        $user->save();

        $message = 'تم التحقق من الحساب بنجاح. سيتم مراجعة طلبك من قبل مدير النظام.';
        $result['data'] = [];
        $result['message'] = $message;

        return $result;
    }

    /**
     * Resend OTP for verification
     *
     * @return array
     */
    public function resendOtp(Request $request)
    {

        $user = User::where('email', $request->username)->orWhere('phone', $request->username)->first();

        if (! $user) {
            $result['message'] = 'User not found';
            $result['data'] = [];

            return $result;
        }

        // Delete existing OTPs
        OtpVerification::where('user_id', $user->id)->delete();

        // Generate and send new OTP
        $this->generateAndSendOtp($user);

        $message = 'تم إرسال رمز تحقق جديد';

        $result['data'] = [];
        $result['message'] = $message;

        return $result;
    }

    /**
     * Check registration status
     *
     * @param  int  $userId
     * @return array
     */
    public function checkRegistrationStatus($userId)
    {
        $user = User::findOrFail($userId);

        return [
            'status' => $user->status,
            'message' => $this->getStatusMessage($user->status),
        ];
    }

    /**
     * Approve user registration (for admin)
     *
     * @param  int  $userId
     * @return array
     */
    public function approveRegistration($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = 'active';
        $user->save();

        // Notify user about approval
        $user->notify(new RegistrationApproved);

        return [
            'user' => $user,
            'message' => 'تم اعتماد التسجيل بنجاح',
        ];
    }

    /**
     * Reject user registration (for admin)
     *
     * @param  int  $userId
     * @param  string  $reason
     * @return array
     */
    public function rejectRegistration($userId, $reason = null)
    {
        $user = User::findOrFail($userId);
        $user->status = 'rejected';
        $user->save();

        // Notify user about rejection
        $user->notify(new RegistrationRejected($reason));

        return [
            'user' => $user,
            'message' => 'تم رفض التسجيل',
        ];
    }

    /**
     * Get status message based on status code
     *
     * @param  string  $status
     * @return string
     */
    private function getStatusMessage($status)
    {
        return match ($status) {
            'pending' => 'طلب التسجيل قيد المراجعة',
            'active' => 'تم اعتماد التسجيل بنجاح',
            'rejected' => 'تم رفض طلب التسجيل',
            default => 'حالة غير معروفة'
        };
    }

    /**
     * Generate and send OTP to user
     *
     * @return void
     */
    private function generateAndSendOtp(User $user)
    {
        // Generate OTP
        // $otp = rand(100000, 999999);
        $otp = 1234;
        // Save OTP
        OtpVerification::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15), // OTP valid for 15 minutes
        ]);

        // Send OTP notification
        $user->notify(new OtpSent($otp));
    }

    /**
     * Notify admin about new registration
     *
     * @return void
     */
    private function notifyAdminAboutNewRegistration(User $user)
    {
        // Get all admin users
        $admins = User::role('admin')->get();

        // Notification logic here (can be customized based on your notification system)
        // Example using Laravel's notification system:
        // Notification::send($admins, new NewUserRegistered($user));
    }

    /**
     * Send password reset link to user
     *
     * @return array
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->username)->orWhere('phone', $request->username)->first();

        if (! $user) {
            $result['data'] = [];
            $result['message'] = 'User not found';

            return $result;
        }

        // Generate and save reset token
        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Send reset link to user
        $user->notify(new \App\Notifications\ResetPasswordNotification($token));

        $message = 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.';
        $result['data'] = [];
        $result['message'] = $message;

        return $result;
    }

    /**
     * Reset user password
     *
     * @return array
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->username)->orWhere('phone', $request->username)->first();

        if (! $user) {

            $result['data'] = [];
            $result['message'] = 'User not found';

            return $result;
        }

        $resetData = DB::table('password_resets')
            ->where('email', $user->email)
            ->first();

        if (! $resetData || ! Hash::check($request->token, $resetData->token)) {
            $message = 'رابط إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.';

            $result['data'] = [];
            $result['message'] = $message;

            return $result;
        }

        // Update user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $user->email)->delete();

        $result['data'] = [];
        $result['message'] = 'تم إعادة تعيين كلمة المرور بنجاح.';

        return $result;

    }
}
