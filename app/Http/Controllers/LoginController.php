<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (! Auth::attempt($credentials)) {
                Log::warning(
                    'Log in attempt with invalid credentials detected',
                    ['email' => $credentials['email']],
                );

                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('wedge-matrix')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'access_token' => $token,
            ], 200);
        } catch (Throwable $e) {
            Log::error(
                'Server error while logging in',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while logging in',
            ], 500);
        }
    }
}
