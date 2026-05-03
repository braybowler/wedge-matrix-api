<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            Password::sendResetLink($request->only('email'));

            return response()->json([
                'message' => 'If an account exists with that email, you will receive a password reset link.',
            ], 200);
        } catch (Throwable $e) {
            Log::error(
                'Server error while sending password reset link',
                ['exception' => $e],
            );

            return response()->json([
                'message' => 'Unexpected error while sending password reset link',
            ], 500);
        }
    }
}
