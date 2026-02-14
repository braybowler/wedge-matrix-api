<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load('wedgeMatrix');

        return response()->json([
            'user' => $user,
        ]);
    }
}
