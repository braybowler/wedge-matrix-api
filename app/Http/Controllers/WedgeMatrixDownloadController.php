<?php

namespace App\Http\Controllers;

use App\Http\Requests\WedgeMatrix\DownloadWedgeMatrixRequest;
use App\Models\WedgeMatrix;
use App\Services\WedgeMatrix\WedgeMatrixDownloadService;
use Symfony\Component\HttpFoundation\Response;

class WedgeMatrixDownloadController extends Controller
{
    public function __invoke(DownloadWedgeMatrixRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixDownloadService $service): Response
    {
        $pdf = $service->generatePdf($wedgeMatrix);

        return $pdf->download('wedge-matrix.pdf');
    }
}