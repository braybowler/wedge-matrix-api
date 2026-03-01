<?php

namespace App\Services\User;

use App\Exceptions\CouldNotDeleteUserException;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserDeletionService
{
    /**
     * @throws CouldNotDeleteUserException
     * @throws Throwable
     */
    public function delete(User $user): void
    {
        try {
            $email = $user->email;
            $user->tokens()->delete();
            $user->delete();
            Mail::to($email)->send(new AccountDeletionMail($email));
        } catch (QueryException $e) {
            Log::error(
                'Failed to delete user',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotDeleteUserException(
                'Could not delete user',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while deleting user',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
