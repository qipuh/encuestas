<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id ?? $this->route('usuario');

        return [
            'name' => 'sometimes|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'email' => ['sometimes', 'email', 'max:180', Rule::unique('users', 'email')->ignore($userId)],
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'telefono' => 'nullable|string|max:20',
            'dni' => 'nullable|string|max:20',
            'habilitado' => 'boolean',
            'fuentes' => 'nullable|array',
            'fuentes.*' => 'exists:fuentes,id',
            'foto' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
