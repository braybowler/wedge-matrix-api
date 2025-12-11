<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Facades\App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } catch (QueryException $e) {
            Log::error(
                'Failed to create user while registering',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Could not create user',
            ], 400);
        } catch (Throwable $e) {
            Log::error(
                'Server error while registering user',
                [$e->getMessage(), $e->getTrace()]
            );

            return response()->json([
                'message' => 'Unexpected error while registering user',
            ], 500);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }
}
