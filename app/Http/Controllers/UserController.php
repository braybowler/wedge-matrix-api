<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotDeleteUserException;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\User\UserDeletionService;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return new UserResource($request->user());
    }

    public function destroy(Request $request, UserDeletionService $userDeletionService)
    {
        try {
            $userDeletionService->delete($request->user());

            return response()->noContent();
        } catch (CouldNotDeleteUserException $e) {
            return response()->json([
                'message' => 'Could not delete user',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected server error while deleting user',
            ], 500);
        }
    }
}
