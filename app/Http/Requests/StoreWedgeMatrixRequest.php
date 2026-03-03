<?php

namespace App\Http\Requests;

use App\Enums\ColumnHeaderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreWedgeMatrixRequest extends FormRequest
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
            'label' => 'nullable|string|max:255',
            'column_header_type' => ['nullable', new Enum(ColumnHeaderType::class)],
        ];
    }
}
