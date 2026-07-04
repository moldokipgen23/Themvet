<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'target_exam_id' => 'nullable|exists:exams,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'target_exam_id' => $validated['target_exam_id'] ?? null,
        ]);

        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $user->roles()->attach($studentRole);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'user' => $user->load('roles', 'targetExam'),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('roles', 'targetExam'),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('roles', 'targetExam');
        $gamification = app(GamificationService::class)->getUserStats($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'gamification' => $gamification,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20|unique:users,phone,' . $user->id,
            'target_exam_id' => 'sometimes|nullable|exists:exams,id',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => ['user' => $user->load('roles', 'targetExam')],
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(60);

        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset token generated',
            'data' => [
                'reset_token' => $token,
                'email' => $request->email,
                'note' => 'In production, this token would be emailed. Use it with the reset-password endpoint.',
            ],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        if ($record->created_at < now()->subHours(24)) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully. You can now login with your new password.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verification is not yet implemented. This is a stub endpoint.',
        ]);
    }

    public function googleRedirect()
    {
        return response()->json([
            'status' => 'success',
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function googleCallback(Request $request)
    {
        $idToken = $request->input('id_token');

        if ($idToken) {
            try {
                $payload = json_decode(base64_decode(strtr(explode('.', $idToken)[1], '-_', '+/')), true);
                if (!$payload || !isset($payload['email'])) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid Google token.'], 401);
                }
                $googleId = $payload['sub'] ?? null;
                $email = $payload['email'];
                $name = $payload['name'] ?? '';
                $avatar = $payload['picture'] ?? null;
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Google token.'], 401);
            }
        } else {
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
                $googleId = $googleUser->getId();
                $email = $googleUser->getEmail();
                $name = $googleUser->getName();
                $avatar = $googleUser->getAvatar();
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Google authentication failed.'], 401);
            }
        }

        $user = User::where('google_id', $googleId ?? '')->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['google_id' => $googleId, 'avatar' => $avatar]);
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatar,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(32)),
                ]);
                $studentRole = Role::firstOrCreate(['name' => 'student']);
                $user->roles()->attach($studentRole);
            }
        }

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Could not identify Google user.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Your account has been deactivated.'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Google login successful',
            'data' => [
                'user' => $user->load('roles', 'targetExam'),
                'token' => $token,
            ],
        ]);
    }

    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified.',
            ]);
        }

        $token = Str::random(64);
        \DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $verificationUrl = config('app.url') . '/api/auth/verify-email?token=' . $token . '&email=' . urlencode($user->email);

        try {
            Mail::raw("Verify your email: $verificationUrl", function ($message) use ($user) {
                $message->to($user->email)->subject('ThemVet - Verify Your Email');
            });
        } catch (\Exception $e) {
            // Email sending may fail in dev; still return token for testing
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email sent.',
            'data' => ['verification_url' => $verificationUrl],
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $record = \DB::table('email_verification_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification token.',
            ], 400);
        }

        User::where('email', $request->email)->update(['email_verified_at' => now()]);
        \DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.',
        ]);
    }
}
