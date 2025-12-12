<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (! Auth::attempt($credentials)) {
                Log::warning(
                    'Log in attempt with invalid credentials detected',
                    [$credentials['email']],
                );

                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = Auth::user();
        } catch (Throwable $e) {
            Log::error(
                'Server error while logging in',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while logging in',
            ], 500);
        }

        $token = $user->createToken('wedge-matrix')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
        ], 200);
    }
}
