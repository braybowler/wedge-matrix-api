<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WedgeMatrixDeleteRequest extends FormRequest
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
        return [];
    }
}
