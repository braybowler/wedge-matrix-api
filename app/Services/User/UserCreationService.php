<?php

namespace App\Services\User;

use App\Exceptions\CouldNotCreateUserException;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserCreationService
{
    /**
     * @throws CouldNotCreateUserException
     * @throws Throwable
     */
    public function create(string $email, string $password): User
    {
        try {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $user->wedgeMatrix()->create([
                'column_headers' => ['25%', '50%', '75%', '100%']
            ]);

            return $user;
        } catch (QueryException $e) {
            Log::error(
                'Failed to create user while registering',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotCreateUserException(
                'Could not create user',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while registering user',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
