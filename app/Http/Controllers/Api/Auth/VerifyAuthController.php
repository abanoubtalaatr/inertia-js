<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GetOtpRequest;
use App\Http\Resources\HotelProfileResource;
use App\Http\Resources\ProviderProfileResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendOtpEmail;
use App\Models\Account;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyAuthController extends Controller
{
    protected $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/verify-email",
     *     summary="Send OTP for email verification",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language for the response (e.g., en, ar)",
     *
     *         @OA\Schema(type="string", example="en")
     *     ),
     *
     *     @OA\Response(response=200, description="OTP sent successfully."),
     *     @OA\Response(response=404, description="Email not found."), * )
     */
    public function sendOtp(GetOtpRequest $request)
    {
        if (Account::where('email', $request->email)->exists()) {
            $user = Account::where('email', $request->email)->first();
            if ($user->email_verified_at != null) {
                return response()->json(['message' => __('messages.email_already_verified')], 400);
            }
        }
        $otp = $this->otpService->generateOtp($request->email, 'email_verification');

        dispatch(new SendOtpEmail($request->email, $otp));

        return response()->json(['message' => __('messages.otp_sent'), 'otp' => $otp]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/verify-otp",
     *     summary="Verify email using OTP",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language for the response (e.g., en, ar)",
     *
     *         @OA\Schema(type="string", example="en")
     *     ),
     *
     *     @OA\Response(response=200, description="OTP sent successfully."),
     *     @OA\Response(response=404, description="Email not found."), * )
     */
    public function verifyEmail(Request $request)
    {
        $email = $request->email;
        $account = Account::where('email', $email)
            ->orWhere('pending_email', $email)
            ->first();

        if (! $account) {
            return response()->json(['message' => __('messages.email_not_found')], 404);
        }

        $types = ['email_verification', 'update_email_verification'];
        if (! in_array($request->type, $types)) {
            return response()->json(['message' => __('messages.invalid_type')], 400);
        }

        if (! $this->otpService->verifyOtp($request->email, $request->otp, $request->type)) {
            return response()->json(['message' => __('messages.invalid_or_expired_otp')], 400);
        }
        if ($request->type === 'update_email_verification') {
            $account->email = $account->pending_email;
            $account->pending_email = null;
            $account->email_verified_at = now();
        } else {
            $account->is_active = true;
            $account->email_verified_at = now();
        }

        $account->save();

        $response = [
            'message' => __('messages.email_verified'),
            // 'data' => new UserResource($account),
            'data' => $account && $account->type == 'hotel' ? new HotelProfileResource($account) : new ProviderProfileResource($account),
        ];

        if ($request->type !== 'update_email_verification') {
            $guard = $account->type;
            $response['token'] = \Auth::guard($guard)->login($account);
        }

        return response()->json($response);
    }
}
