<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Your password has been reset.',
                ], 200);
            }

            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 400);
        } catch (Throwable $e) {
            Log::error(
                'Server error while resetting password',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while resetting password',
            ], 500);
        }
    }
}
