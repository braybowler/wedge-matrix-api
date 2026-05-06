<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\User\UserDeletionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('wedgeMatrices'));
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return new UserResource($request->user()->load('wedgeMatrices'));
    }

    public function destroy(Request $request, UserDeletionService $userDeletionService): Response
    {
        $userDeletionService->delete($request->user());

        return response()->noContent();
    }
}