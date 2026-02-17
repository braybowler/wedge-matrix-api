<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('wedgeMatrix');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->user()->update(['has_dismissed_tutorial' => true]);

        return response()->json(['message' => 'Tutorial dismissed.']);
    }
}
