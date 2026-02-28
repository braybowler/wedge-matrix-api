<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotDownloadWedgeMatrixException;
use App\Http\Requests\WedgeMatrixDownloadRequest;
use App\Models\WedgeMatrix;
use App\Services\WedgeMatrix\WedgeMatrixDownloadService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class WedgeMatrixDownloadController extends Controller
{
    public function __invoke(WedgeMatrixDownloadRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixDownloadService $service): Response
    {
        try {
            $pdf = $service->generatePdf($wedgeMatrix);

            return $pdf->download('wedge-matrix.pdf');
        } catch (CouldNotDownloadWedgeMatrixException $e) {
            return response()->json([
                'message' => 'Could not download wedge matrix',
            ], 400);
        } catch (Throwable $e) {
            Log::error(
                'Server error while downloading wedge matrix: (GET /api/wedge-matrix/{wedgeMatrix}/download)',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected server error while downloading wedge matrix',
            ], 500);
        }
    }
}
