<?php

namespace App\Http\Controllers;

use App\Exceptions\CannotDeleteLastWedgeMatrixException;
use App\Exceptions\CouldNotCreateWedgeMatrixException;
use App\Exceptions\CouldNotDeleteWedgeMatrixException;
use App\Exceptions\CouldNotUpdateWedgeMatrixException;
use App\Exceptions\WedgeMatrixLimitReachedException;
use App\Http\Requests\WedgeMatrixDeleteRequest;
use App\Http\Requests\WedgeMatrixUpdateRequest;
use App\Http\Resources\WedgeMatrixResource;
use App\Models\WedgeMatrix;
use App\Repositories\WedgeMatrix\WedgeMatrixRepository;
use App\Services\WedgeMatrix\WedgeMatrixCreationService;
use App\Services\WedgeMatrix\WedgeMatrixDeletionService;
use App\Services\WedgeMatrix\WedgeMatrixUpdateService;
use Illuminate\Http\Request;
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

    public function store(Request $request, WedgeMatrixCreationService $wedgeMatrixCreationService)
    {
        try {
            $wedgeMatrix = $wedgeMatrixCreationService->create($request->user(), $request->input('label'));

            return (new WedgeMatrixResource($wedgeMatrix))
                ->response()
                ->setStatusCode(201);
        } catch (WedgeMatrixLimitReachedException $e) {
            return response()->json([
                'message' => 'Wedge matrix limit reached',
            ], 422);
        } catch (CouldNotCreateWedgeMatrixException $e) {
            return response()->json([
                'message' => 'Could not create wedge matrix',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected server error while creating wedge matrix',
            ], 500);
        }
    }

    public function update(WedgeMatrixUpdateRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixUpdateService $wedgeMatrixUpdateService)
    {
        try {
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

    public function destroy(WedgeMatrixDeleteRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixDeletionService $wedgeMatrixDeletionService)
    {
        try {
            $wedgeMatrixDeletionService->delete($wedgeMatrix);

            return response()->noContent();
        } catch (CannotDeleteLastWedgeMatrixException $e) {
            return response()->json([
                'message' => 'Cannot delete the last wedge matrix',
            ], 422);
        } catch (CouldNotDeleteWedgeMatrixException $e) {
            return response()->json([
                'message' => 'Could not delete wedge matrix',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected server error while deleting wedge matrix',
            ], 500);
        }
    }
}
