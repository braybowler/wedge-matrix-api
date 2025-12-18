<?php

namespace App\Repositories\WedgeMatrix;

use App\Models\WedgeMatrix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WedgeMatrixRepository
{
    public function index(): Builder
    {
        return WedgeMatrix::query()->where('user_id', Auth::id());
    }
}
