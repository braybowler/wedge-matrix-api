<?php

namespace App\Services\WedgeMatrix;

use App\Exceptions\CouldNotCreateWedgeMatrixException;
use App\Exceptions\WedgeMatrixLimitReachedException;
use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixCreationService
{
    /**
     * @throws WedgeMatrixLimitReachedException
     * @throws CouldNotCreateWedgeMatrixException
     * @throws Throwable
     */
    public function create(User $user, ?string $label = null): WedgeMatrix
    {
        if ($user->wedgeMatrices()->count() >= WedgeMatrix::MAX_PER_USER) {
            throw new WedgeMatrixLimitReachedException(
                'Wedge matrix limit reached'
            );
        }

        $emptyCell = ['carry_value' => null, 'total_value' => null];
        $emptyRow = array_fill(0, WedgeMatrix::DEFAULT_COLUMNS, $emptyCell);
        $defaultYardageValues = array_fill(0, count(WedgeMatrix::DEFAULT_CLUBS), $emptyRow);

        try {
            return $user->wedgeMatrices()->create([
                'label' => $label,
                'number_of_rows' => count(WedgeMatrix::DEFAULT_CLUBS),
                'number_of_columns' => WedgeMatrix::DEFAULT_COLUMNS,
                'column_headers' => WedgeMatrix::DEFAULT_COLUMN_HEADERS,
                'club_labels' => WedgeMatrix::DEFAULT_CLUBS,
                'selected_row_display_option' => 'Both',
                'yardage_values' => $defaultYardageValues,
            ]);
        } catch (QueryException $e) {
            Log::error(
                'Failed to create wedge matrix',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotCreateWedgeMatrixException(
                'Could not create wedge matrix',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while creating wedge matrix',
                [$e->getMessage(), $e->getTrace()]
            );

            throw $e;
        }
    }
}
