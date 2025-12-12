<?php

namespace App\Http\Controllers;

use App\Http\Requests\WedgeMatrixUpdateRequest;
use App\Http\Resources\WedgeMatrixResource;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class WedgeMatrixController extends Controller
{
    public function index()
    {
        try {
            return WedgeMatrixResource::collection(
                WedgeMatrix::query()->where(
                    'user_id',
                    Auth::user()->id
                )->get()
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

    public function update(WedgeMatrixUpdateRequest $request)
    {
        try {
            $wedgeMatrix =  WedgeMatrix::where('user_id', Auth::user()->id);

            $wedgeMatrix->update($request->validated());

            return response()->noContent();
        } catch (Throwable $e) {
            Log::error(
                'Server error while updating wedge matrices: (PUT /api/wedge-matrix)',
                [$e->getMessage(), $e->getTrace()],
            );

            return response()->json([
                'message' => 'Unexpected error while updating wedge matrix',
            ], 500);
        }
    }
}
