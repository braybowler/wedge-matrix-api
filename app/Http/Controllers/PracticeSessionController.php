<?php

namespace App\Http\Controllers;

use App\Http\Requests\PracticeSession\DeletePracticeSessionRequest;
use App\Http\Requests\PracticeSession\StorePracticeSessionRequest;
use App\Http\Resources\PracticeSessionResource;
use App\Models\PracticeSession;
use App\Repositories\PracticeSession\PracticeSessionRepository;
use App\Services\PracticeSession\PracticeSessionCreationService;
use App\Services\PracticeSession\PracticeSessionDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PracticeSessionController extends Controller
{
    public function index(PracticeSessionRepository $practiceSessionRepository): AnonymousResourceCollection
    {
        return PracticeSessionResource::collection(
            $practiceSessionRepository->index()->get()
        );
    }

    public function store(StorePracticeSessionRequest $request, PracticeSessionCreationService $practiceSessionCreationService): JsonResponse
    {
        $practiceSession = $practiceSessionCreationService->create(
            $request->user(),
            $request->validated(),
        );

        return (new PracticeSessionResource($practiceSession))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(DeletePracticeSessionRequest $request, PracticeSession $practiceSession, PracticeSessionDeletionService $practiceSessionDeletionService): Response
    {
        $practiceSessionDeletionService->delete($practiceSession);

        return response()->noContent();
    }
}