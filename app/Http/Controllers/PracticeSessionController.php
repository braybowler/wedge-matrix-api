<?php

namespace App\Http\Controllers;

use App\Exceptions\CouldNotCreatePracticeSessionException;
use App\Exceptions\CouldNotDeletePracticeSessionException;
use App\Http\Requests\DeletePracticeSessionRequest;
use App\Http\Requests\StorePracticeSessionRequest;
use App\Http\Resources\PracticeSessionResource;
use App\Models\PracticeSession;
use App\Repositories\PracticeSession\PracticeSessionRepository;
use App\Services\PracticeSession\PracticeSessionCreationService;
use App\Services\PracticeSession\PracticeSessionDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class PracticeSessionController extends Controller
{
    public function index(PracticeSessionRepository $practiceSessionRepository): AnonymousResourceCollection|JsonResponse
    {
        try {
            return PracticeSessionResource::collection(
                $practiceSessionRepository->index()->get()
            );
        } catch (Throwable $e) {
            Log::error(
                'Server error while fetching practice sessions: (GET /api/practice-session)',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while fetching practice sessions',
            ], 500);
        }
    }

    public function store(StorePracticeSessionRequest $request, PracticeSessionCreationService $practiceSessionCreationService): JsonResponse
    {
        try {
            $practiceSession = $practiceSessionCreationService->create(
                $request->user(),
                $request->validated(),
            );

            return (new PracticeSessionResource($practiceSession))
                ->response()
                ->setStatusCode(201);
        } catch (CouldNotCreatePracticeSessionException $e) {
            return response()->json([
                'message' => 'Could not create practice session',
            ], 400);
        } catch (Throwable $e) {
            Log::error(
                'Server error while creating practice session: (POST /api/practice-session)',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected server error while creating practice session',
            ], 500);
        }
    }

    public function destroy(DeletePracticeSessionRequest $request, PracticeSession $practiceSession, PracticeSessionDeletionService $practiceSessionDeletionService): Response|JsonResponse
    {
        try {
            $practiceSessionDeletionService->delete($practiceSession);

            return response()->noContent();
        } catch (CouldNotDeletePracticeSessionException $e) {
            return response()->json([
                'message' => 'Could not delete practice session',
            ], 400);
        } catch (Throwable $e) {
            Log::error(
                'Server error while deleting practice session: (DELETE /api/practice-session/{id})',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected server error while deleting practice session',
            ], 500);
        }
    }
}
