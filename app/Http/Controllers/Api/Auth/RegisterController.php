<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GetOtpRequest;
use App\Http\Requests\RegisterAccountRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendOtpEmail;
use App\Models\Account;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/api/provider/register",
     *     summary="Register a new Provider",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Provider Name"),
     *             @OA\Property(property="email", type="string", example="provider@example.com"),
     *             @OA\Property(property="phone", type="string", example="+123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Provider registration successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Provider registration successful"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *
     *      @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language for the response (e.g., en, ar)",
     *
     *         @OA\Schema(type="string", example="en")
     *     ),
     * )
     */
    public function registerProvider(RegisterAccountRequest $request)
    {
        $account = Account::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'source' => 'web_site',
            'password' => Hash::make($request->password),
            'type' => 'provider',
            'job_role_id' => $request->job_role_id,
            'commercial_register' => $request->commercial_register,

        ]);

        $otp = $this->otpService->generateOtp($request->email, 'email_verification');
        dispatch(new SendOtpEmail($request->email, $otp));

        return response()->json([
            'message' => __('messages.account_created'),
            'user' => new UserResource($account),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/hotel/register",
     *     summary="Register a new Hotel",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Hotel Name"),
     *             @OA\Property(property="email", type="string", example="hotel@example.com"),
     *             @OA\Property(property="phone", type="string", example="+123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Hotel registration successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Hotel registration successful"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *
     *      @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language for the response (e.g., en, ar)",
     *
     *         @OA\Schema(type="string", example="en")
     *     ),
     * )
     */
    public function registerHotel(RegisterAccountRequest $request)
    {
        $account = Account::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'source' => 'web_site',
            'password' => Hash::make($request->password),
            'type' => 'hotel',
            'commercial_register' => $request->commercial_register,

        ]);
        $otp = $this->otpService->generateOtp($request->email, 'email_verification');
        dispatch(new SendOtpEmail($request->email, $otp));

        return response()->json([
            'message' => __('messages.account_created'),
            'user' => new UserResource($account),
        ], 201);
    }

    public function ResendOtp(GetOtpRequest $request, OTPService $otpService)
    {
        $model = Account::class;

        return $otpService->resendOtp($request->email, $model);
    }
}
