<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgotPasswordRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Requests\API\Auth\ResendOtpRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\VerifyOtpRequest;
use App\Http\Resources\Api\ProfileResource;
use App\Http\Resources\Api\SimpleUserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    protected $userService;

    /**
     * Register a new user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->userService->registerUser($request);

        return $this->success($result, $result['message'], 201);
    }

    /**
     * Login user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->username)
            ->orWhere('phone', $request->username)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if (! $user->verified) {
            return $this->error('Your account is not verified');
        }

        if ($user->status == 'pending' || $user->status == 'rejected') {
            return $this->error('Your account is '.$user->status);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => SimpleUserResource::make($user),
            'token' => $token,
        ], 'Logged in successfully');
    }

    /**
     * Get user profile
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'specialist') {
            $loadRelations[] = 'company';
        }

        return $this->success(ProfileResource::make($user));
    }

    /**
     * Update user profile
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        $rules = [
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'preferred_language' => 'nullable|string|in:ar,en',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Add role-specific validation rules
        if ($role === 'specialist') {
            $rules = array_merge($rules, [
                'specialization' => 'nullable|string',
                'qualification' => 'nullable|string',
                'experience_years' => 'nullable|integer',
                'bio' => 'nullable|string',
                'available_days' => 'nullable|array',
                'available_hours' => 'nullable|array',
            ]);
        } elseif ($role === 'company') {
            $rules = array_merge($rules, [
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        // Handle file uploads
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        if ($role === 'company' && $request->hasFile('logo')) {
            $path = $request->file('logo')->store('company_logos', 'public');
            $user->logo = $path;
        }

        // Update basic fields for all roles
        $user->fill($request->only([
            'name', 'phone', 'address', 'province_id', 'preferred_language',
        ]));

        // Update role-specific fields
        if ($role === 'specialist') {
            $user->fill($request->only([
                'specialization', 'qualification', 'experience_years',
                'bio', 'available_days', 'available_hours',
            ]));
        } elseif ($role === 'company') {
            $user->fill($request->only([
                'description',
            ]));
        }

        $user->save();

        return $this->success(ProfileResource::make($user), 'Profile updated successfully');
    }

    /**
     * Change user password
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->userService->verifyOtp($request);

        return $this->success($result['data'], $result['message']);
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $result = $this->userService->resendOtp($request);

        return $this->success($result['data'], $result['message']);
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $result = $this->userService->sendResetLinkEmail($request);

        return $this->success($result['data'], $result['message']);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $result = $this->userService->resetPassword($request);

        return $this->success($result['data'], $result['message']);
    }
}
