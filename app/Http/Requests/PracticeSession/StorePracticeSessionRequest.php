<?php

namespace App\Http\Requests\PracticeSession;

use Illuminate\Foundation\Http\FormRequest;

class StorePracticeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wedge_matrix_id' => [
                'nullable',
                'integer',
                'exists:wedge_matrices,id',
                function ($attribute, $value, $fail) {
                    if ($value && \App\Models\WedgeMatrix::where('id', $value)->where('user_id', $this->user()->id)->doesntExist()) {
                        $fail('The selected wedge matrix does not belong to you.');
                    }
                },
            ],
            'mode' => 'sometimes|string|in:gauntlet,drill',
            'shot_count' => 'required|integer|min:1',
            'shots' => 'required|array',
            'shots.*' => 'required|array',
            'shots.*.shot_number' => 'required|integer|min:1',
            'shots.*.target_yards' => 'required|numeric|min:0',
            'shots.*.actual_carry' => 'required|numeric|min:0|max:999',
            'shots.*.difference' => 'required|numeric|min:0',
            'shots.*.club_label' => 'sometimes|string',
            'shots.*.swing_label' => 'sometimes|string',
            'average_difference' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $shotCount = $this->input('shot_count');
            $shots = $this->input('shots');

            if (is_array($shots) && is_numeric($shotCount) && count($shots) !== (int) $shotCount) {
                $validator->errors()->add('shots', 'The number of shots must match the shot count.');
            }
        });
    }
}
