<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'sometimes|in:pendiente,en_proceso,resuelto',
            'cliente_nombre' => 'sometimes|nullable|string|max:120',
            'cliente_dni' => 'sometimes|nullable|string|max:15',
            'cliente_telefono' => 'sometimes|nullable|string|max:20',
            'cliente_email' => 'sometimes|nullable|email|max:120',
        ];
    }

    public function messages(): array
    {
        return [
            'estado.in' => 'El estado debe ser pendiente, en_proceso o resuelto.',
        ];
    }
}
