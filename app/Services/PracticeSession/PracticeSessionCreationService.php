<?php

namespace App\Services\PracticeSession;

use App\Exceptions\CouldNotCreatePracticeSessionException;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class PracticeSessionCreationService
{
    /**
     * @throws CouldNotCreatePracticeSessionException
     * @throws Throwable
     */
    public function create(User $user, array $data): PracticeSession
    {
        try {
            return $user->practiceSessions()->create($data);
        } catch (QueryException $e) {
            Log::error(
                'Failed to create practice session',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotCreatePracticeSessionException(
                'Could not create practice session',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while creating practice session',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
