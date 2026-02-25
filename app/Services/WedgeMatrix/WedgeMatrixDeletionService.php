<?php

namespace App\Services\WedgeMatrix;

use App\Exceptions\CannotDeleteLastWedgeMatrixException;
use App\Exceptions\CouldNotDeleteWedgeMatrixException;
use App\Models\WedgeMatrix;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixDeletionService
{
    /**
     * @throws CannotDeleteLastWedgeMatrixException
     * @throws CouldNotDeleteWedgeMatrixException
     * @throws Throwable
     */
    public function delete(WedgeMatrix $wedgeMatrix): void
    {
        if ($wedgeMatrix->user()->first()->wedgeMatrices()->count() <= 1) {
            throw new CannotDeleteLastWedgeMatrixException(
                'Cannot delete the last wedge matrix'
            );
        }

        try {
            $wedgeMatrix->delete();
        } catch (QueryException $e) {
            Log::error(
                'Failed to delete wedge matrix',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotDeleteWedgeMatrixException(
                'Could not delete wedge matrix',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while deleting wedge matrix',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
