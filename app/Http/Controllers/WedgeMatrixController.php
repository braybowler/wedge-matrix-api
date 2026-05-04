<?php

namespace App\Http\Controllers;

use App\Enums\ColumnHeaderType;
use App\Http\Requests\WedgeMatrix\DeleteWedgeMatrixRequest;
use App\Http\Requests\WedgeMatrix\StoreWedgeMatrixRequest;
use App\Http\Requests\WedgeMatrix\UpdateWedgeMatrixRequest;
use App\Http\Resources\WedgeMatrixResource;
use App\Models\WedgeMatrix;
use App\Repositories\WedgeMatrix\WedgeMatrixRepository;
use App\Services\WedgeMatrix\WedgeMatrixCreationService;
use App\Services\WedgeMatrix\WedgeMatrixDeletionService;
use App\Services\WedgeMatrix\WedgeMatrixUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WedgeMatrixController extends Controller
{
    public function index(WedgeMatrixRepository $wedgeMatrixRepository): AnonymousResourceCollection
    {
        return WedgeMatrixResource::collection(
            $wedgeMatrixRepository->index()->get()
        );
    }

    public function store(StoreWedgeMatrixRequest $request, WedgeMatrixCreationService $wedgeMatrixCreationService): JsonResponse
    {
        $wedgeMatrix = $wedgeMatrixCreationService->create(
            $request->user(),
            $request->input('label'),
            ColumnHeaderType::tryFrom($request->input('column_header_type')),
        );

        return (new WedgeMatrixResource($wedgeMatrix))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateWedgeMatrixRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixUpdateService $wedgeMatrixUpdateService): Response
    {
        $wedgeMatrixUpdateService->update($wedgeMatrix, $request->validated());

        return response()->noContent();
    }

    public function destroy(DeleteWedgeMatrixRequest $request, WedgeMatrix $wedgeMatrix, WedgeMatrixDeletionService $wedgeMatrixDeletionService): Response
    {
        $wedgeMatrixDeletionService->delete($wedgeMatrix);

        return response()->noContent();
    }
}