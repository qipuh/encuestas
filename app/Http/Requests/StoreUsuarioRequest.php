<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'email' => 'required|email|max:180|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
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
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role_id.required' => 'Debes asignar un rol.',
            'role_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
