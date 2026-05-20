<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_actual' => 'required|string',
            'password_nuevo' => 'required|string|min:8|confirmed',
            'password_nuevo_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'password_actual.required' => 'La contraseña actual es obligatoria.',
            'password_nuevo.required' => 'La nueva contraseña es obligatoria.',
            'password_nuevo.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password_nuevo.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password_nuevo_confirmation.required' => 'Debes confirmar la nueva contraseña.',
        ];
    }
}
