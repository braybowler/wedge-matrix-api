<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WedgeMatrixUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('wedgeMatrix')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => 'sometimes|string|max:255',
            'number_of_columns' => 'sometimes|integer|min:1|max:4',
            'column_headers' => 'sometimes|array|min:1|max:4',
            'column_headers.*' => 'required_with:column_headers|string|max:255',
            'club_labels' => 'sometimes|array|min:1|max:6',
            'club_labels.*' => 'required_with:club_labels|string|in:LW,SW,GW,AW,UW,PW',
            'selected_row_display_option' => [
                'sometimes',
                'string',
                Rule::in(['Both', 'Carry', 'Total']),
            ],
            'yardage_values' => 'sometimes|array|min:1|max:6',
            'yardage_values.*' => 'required|array|min:1|max:4',
            'yardage_values.*.*' => 'required|array',
            'yardage_values.*.*.carry_value' => 'nullable|numeric|min:0',
            'yardage_values.*.*.total_value' => 'nullable|numeric|min:0',
        ];
    }
}
