<?php

namespace App\Services\User;

use App\Exceptions\CouldNotCreateUserException;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserCreationService
{
    /**
     * @throws CouldNotCreateUserException
     * @throws Throwable
     */
    public function create(string $email, string $password, bool $tosAccepted = false): User
    {
        try {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($password),
                'tos_accepted_at' => $tosAccepted ? now() : null,
            ]);

            $user->wedgeMatrices()->create([
                'column_headers' => WedgeMatrix::DEFAULT_COLUMN_HEADERS,
                'club_labels' => WedgeMatrix::DEFAULT_CLUBS,
            ]);

            Mail::to($user->email)->send(new WelcomeMail($user));

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
