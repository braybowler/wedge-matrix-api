<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\User\UserCreationService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, UserCreationService $userCreationService): JsonResponse
    {
        $user = $userCreationService->create(
            email: $request->email,
            password: $request->password,
            tosAccepted: $request->tos_accepted
        );

        $user->load('wedgeMatrices');

        return (new UserResource($user))
            ->additional(['message' => 'User registered successfully'])
            ->response()
            ->setStatusCode(201);
    }
}