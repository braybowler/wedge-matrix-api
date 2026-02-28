<?php

namespace App\Services\WedgeMatrix;

use App\Exceptions\CouldNotUpdateWedgeMatrixException;
use App\Models\WedgeMatrix;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixUpdateService
{
    /**
     * @throws CouldNotUpdateWedgeMatrixException
     * @throws Throwable
     */
    public function update(WedgeMatrix $wedgeMatrix, array $properties): void
    {
        try {
            $wedgeMatrix->update($properties);
        } catch (QueryException $e) {
            Log::error(
                'Failed to update wedge matrix',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotUpdateWedgeMatrixException(
                'Could not update wedge matrix',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while updating wedge matrix',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
