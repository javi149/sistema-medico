<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class StoreProfessionalProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificamos que el usuario exista y tenga el rol de administrador
        return $user !== null && $user->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|unique:professional_profiles,user_id',
            'bio' => 'nullable|string|max:1000',
            'consultation_duration_minutes' => 'required|integer|min:15|max:60',
            'specialties' => 'required|array|min:1',
            'specialties.*' => 'exists:specialties,id',
        ];
    }
}