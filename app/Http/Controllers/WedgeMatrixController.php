<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotUpdateWedgeMatrixException;
use App\Http\Requests\WedgeMatrixUpdateRequest;
use App\Http\Resources\WedgeMatrixResource;
use App\Models\WedgeMatrix;
use App\Repositories\WedgeMatrix\WedgeMatrixRepository;
use App\Services\WedgeMatrix\WedgeMatrixUpdateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixController extends Controller
{
    public function index(WedgeMatrixRepository $wedgeMatrixRepository)
    {
        try {
            return WedgeMatrixResource::collection(
                $wedgeMatrixRepository->index()->get()
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while fetching wedge matrices: (GET /api/wedge-matrix)',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while fetching wedge matrices',
            ], 500);
        }
    }

    public function update(WedgeMatrixUpdateRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixUpdateService $wedgeMatrixUpdateService)
    {
        try {
            if ($wedgeMatrix->user_id !== Auth::id()) {
                return response()->json(
                    ['message' => 'Forbidden'],
                    403
                );
            }

            $wedgeMatrixUpdateService->update($wedgeMatrix, $request->validated());

            return response()->noContent();
        } catch (CouldNotUpdateWedgeMatrixException $e) {
            return response()->json([
                'message' => 'Could not update wedge matrix',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected server error while updating wedge matrix',
            ], 500);
        }
    }
}
