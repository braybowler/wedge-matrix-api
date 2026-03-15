<?php

namespace App\Http\Resources;

use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PracticeSession */
class PracticeSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'wedge_matrix_id' => $this->wedge_matrix_id,
            'mode' => $this->mode,
            'shot_count' => $this->shot_count,
            'shots' => $this->shots,
            'average_difference' => $this->average_difference,
            'created_at' => $this->created_at,
        ];
    }
}
