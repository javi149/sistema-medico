<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Por ahora lo dejamos en true para permitir que pase la validación de roles de Spatie que pondremos después
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:specialties,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
