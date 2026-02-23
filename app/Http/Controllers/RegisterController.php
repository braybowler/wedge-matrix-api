<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotCreateUserException;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\User\UserCreationService;
use Throwable;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, UserCreationService $userCreationService)
    {
        try {
            $user = $userCreationService->create(
                email: $request->email,
                password: $request->password,
                tosAccepted: $request->tos_accepted
            );
        } catch (CouldNotCreateUserException $e) {
            return response()->json([
                'message' => 'Could not create user',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected error while registering user',
            ], 500);
        }

        return (new UserResource($user))
            ->additional(['message' => 'User registered successfully'])
            ->response()
            ->setStatusCode(201);
    }
}
