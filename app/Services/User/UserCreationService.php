<?php

namespace App\Services\User;

use App\Exceptions\CouldNotCreateUserException;
use App\Exceptions\CouldNotCreateWedgeMatrixException;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Services\WedgeMatrix\WedgeMatrixCreationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserCreationService
{
    public function __construct(
        private readonly WedgeMatrixCreationService $wedgeMatrixCreationService,
    ) {}

    /**
     * @throws CouldNotCreateUserException
     * @throws Throwable
     */
    public function create(string $email, string $password, bool $tosAccepted = false): User
    {
        try {
            $user = DB::transaction(function () use ($email, $password, $tosAccepted) {
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make($password),
                    'tos_accepted_at' => $tosAccepted ? now() : null,
                ]);

                $this->wedgeMatrixCreationService->create($user);

                return $user;
            });

            Mail::to($user->email)->send(new WelcomeEmail);

            return $user;
        } catch (QueryException|CouldNotCreateWedgeMatrixException $e) {
            Log::error(
                'Failed to create user while registering',
                ['exception' => $e],
            );

            throw new CouldNotCreateUserException(
                'Could not create user',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while registering user',
                ['exception' => $e]
            );

            throw $e;
        }
    }
}