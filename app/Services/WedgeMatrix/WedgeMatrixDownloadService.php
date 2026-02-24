<?php

namespace App\Services\WedgeMatrix;

use App\Exceptions\CouldNotDownloadWedgeMatrixException;
use App\Models\WedgeMatrix;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixDownloadService
{
    /**
     * @throws CouldNotDownloadWedgeMatrixException
     * @throws Throwable
     */
    public function generatePdf(WedgeMatrix $wedgeMatrix): DomPDF
    {
        try {
            return Pdf::loadView('pdf.wedge-matrix', ['wedgeMatrix' => $wedgeMatrix])
                ->setPaper('letter', 'landscape');
        } catch (Throwable $e) {
            Log::error(
                'Failed to generate wedge matrix PDF',
                [$e->getMessage(), $e->getTrace()],
            );

            throw new CouldNotDownloadWedgeMatrixException(
                'Could not generate wedge matrix PDF',
                previous: $e
            );
        }
    }
}
