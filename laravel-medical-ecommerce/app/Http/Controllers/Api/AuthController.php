<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyRegistrationOtpRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register user and send OTP. No token is issued until OTP verification.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        
        $otp = (string) random_int(1000, 9999);
        $user->otp = $otp;
        $user->phone_verified_at = null;
        $user->save();

        Log::info("OTP for {$user->phone} is $otp");

        return response()->json([
            'message' => 'Account created. Verify OTP to continue.',
            'phone' => $user->phone,
            'otp_simulated' => $otp,
            'requires_otp_verification' => true,
        ], 201);
    }

    /**
     * Verify register OTP and issue auth token.
     */
    public function verifyRegistrationOtp(VerifyRegistrationOtpRequest $request)
    {
        $user = User::where('phone', $request->phone)->firstOrFail();

        if ((string) $user->otp !== (string) $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP code',
            ], 422);
        }

        $user->otp = null;
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

    /**
     * Login using phone and password without OTP.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->phone_verified_at) {
            return response()->json([
                'message' => 'Phone number is not verified yet. Please verify OTP after register.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Login successful'
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

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,'.$user->id,
            'email' => 'nullable|email|unique:users,email,'.$user->id,
        ]);

        $user->update($request->only(['name', 'phone', 'email']));

        return new UserResource($user);
    }

    /**
     * Forgot Password (Simulated)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone']);
        
        $otp = (string) random_int(1000, 9999);
        Log::info("Password reset OTP for {$request->phone} is $otp");

        return response()->json([
            'message' => 'Reset OTP sent successfully.',
            'otp_simulated' => $otp
        ]);
    }
}
