<?php

namespace App\Repositories\PracticeSession;

use App\Models\PracticeSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PracticeSessionRepository
{
    public function index(): Builder
    {
        return PracticeSession::query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
    }
}
