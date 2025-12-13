<?php

namespace App\Http\Resources;

use App\Models\WedgeMatrix;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WedgeMatrix */
class WedgeMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'label' => $this->label,
            'number_of_rows' => $this->number_of_rows,
            'number_of_columns' => $this->number_of_columns,
            'column_headers' => $this->column_headers,
            'selected_row_display_option' => $this->selected_row_display_option,
            'values' => $this->values,
        ];
    }
}
