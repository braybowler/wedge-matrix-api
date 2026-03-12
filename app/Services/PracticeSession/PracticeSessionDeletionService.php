<?php

namespace App\Services\PracticeSession;

use App\Exceptions\CouldNotDeletePracticeSessionException;
use App\Models\PracticeSession;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class PracticeSessionDeletionService
{
    /**
     * @throws CouldNotDeletePracticeSessionException
     * @throws Throwable
     */
    public function delete(PracticeSession $practiceSession): void
    {
        try {
            $practiceSession->delete();
        } catch (QueryException $e) {
            Log::error(
                'Failed to delete practice session',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotDeletePracticeSessionException(
                'Could not delete practice session',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while deleting practice session',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
