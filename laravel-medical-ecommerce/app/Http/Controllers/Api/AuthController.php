<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyRegistrationOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Support\SyrianPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    /**
     * Register user and send OTP. No token is issued until OTP verification.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if ($user?->phone_verified_at) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $isNewUser = $user === null;
        $user ??= new User;
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->save();

        try {
            $otpPayload = $this->requestOtp($user, $request, 'signup');
        } catch (Throwable $exception) {
            if ($isNewUser) {
                $user->delete();
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Account created. WhatsApp accepted the verification request.',
            'phone' => $user->phone,
            'request_id' => $otpPayload['request_id'],
            'delivery_status' => $otpPayload['delivery_status'],
            'expires_in' => $otpPayload['expires_in'],
            'code_length' => $otpPayload['code_length'],
            'requires_otp_verification' => true,
        ], 202);
    }

    /**
     * Verify register OTP and issue auth token.
     */
    public function verifyRegistrationOtp(VerifyRegistrationOtpRequest $request)
    {
        $user = User::where('phone', $request->phone)->firstOrFail();

        $requestId = $request->validated('request_id') ?? $user->qverify_request_id;
        $verified = $this->otpService->verify(
            $user->phone,
            (string) $request->otp,
            $requestId,
        );

        if (! $verified) {
            return response()->json([
                'message' => 'The verification code is invalid or expired.',
            ], 422);
        }

        $user->otp = null;
        $user->qverify_request_id = null;
        $user->qverify_expires_at = null;
        $user->phone_verified_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Phone verified successfully',
        ]);
    }

    public function resendRegistrationOtp(Request $request)
    {
        $request->merge([
            'phone' => SyrianPhoneNumber::normalize($request->input('phone')),
        ]);
        $request->validate([
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX, 'exists:users,phone'],
        ]);

        $user = User::where('phone', $request->phone)->firstOrFail();
        if ($user->phone_verified_at) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already verified.'],
            ]);
        }

        $otpPayload = $this->requestOtp($user, $request, 'signup');

        return response()->json([
            'message' => 'WhatsApp accepted a new verification request.',
            'phone' => $user->phone,
            'request_id' => $otpPayload['request_id'],
            'delivery_status' => $otpPayload['delivery_status'],
            'expires_in' => $otpPayload['expires_in'],
            'code_length' => $otpPayload['code_length'],
        ], 202);
    }

    /**
     * Login using phone and password without OTP.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $user->phone_verified_at) {
            return response()->json([
                'message' => 'Phone number is not verified yet. Please verify OTP after register.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update User Profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if ($request->has('phone')) {
            $request->merge([
                'phone' => SyrianPhoneNumber::normalize($request->input('phone')),
            ]);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => ['sometimes', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX, 'unique:users,phone,'.$user->id],
            'email' => 'nullable|email|unique:users,email,'.$user->id,
        ]);

        $user->update($request->only(['name', 'phone', 'email']));

        return new UserResource($user);
    }

    /** Send a password recovery OTP through QVerify. */
    public function forgotPassword(Request $request)
    {
        $request->merge([
            'phone' => SyrianPhoneNumber::normalize($request->input('phone')),
        ]);
        $request->validate([
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX, 'exists:users,phone'],
        ]);

        $user = User::where('phone', $request->phone)->firstOrFail();
        $otpPayload = $this->requestOtp($user, $request, 'recovery');

        return response()->json([
            'message' => 'WhatsApp accepted the reset code request.',
            'request_id' => $otpPayload['request_id'],
            'delivery_status' => $otpPayload['delivery_status'],
            'expires_in' => $otpPayload['expires_in'],
            'code_length' => $otpPayload['code_length'],
        ], 202);
    }

    /** @return array<string, mixed> */
    private function requestOtp(User $user, Request $request, string $purpose): array
    {
        $payload = $this->otpService->request(
            phone: $user->phone,
            purpose: $purpose,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $user->qverify_request_id = $payload['request_id'];
        $user->qverify_expires_at = now()->addSeconds((int) $payload['expires_in']);
        $user->save();

        Log::info('QVerify OTP request created.', [
            'user_id' => $user->id,
            'purpose' => $purpose,
            'delivery_status' => $payload['delivery_status'],
            'expires_in' => $payload['expires_in'],
        ]);

        return $payload;
    }
}
