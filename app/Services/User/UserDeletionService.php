<?php

namespace App\Services\User;

use App\Exceptions\CouldNotDeleteUserException;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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
        $email = $user->email;

        try {
            DB::transaction(function () use ($user) {
                $user->tokens()->delete();
                $user->delete();
            });

            Mail::to($email)->send(new AccountDeletionMail);
        } catch (QueryException $e) {
            Log::error(
                'Failed to delete user',
                ['exception' => $e],
            );

            throw new CouldNotDeleteUserException(
                'Could not delete user',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while deleting user',
                ['exception' => $e]
            );

            throw $e;
        }
    }
}
