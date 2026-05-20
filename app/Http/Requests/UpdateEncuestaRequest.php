<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:160',
            'fuente_id' => 'sometimes|exists:fuentes,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'pregunta_principal' => 'sometimes|string|max:500',
            'pregunta_positiva' => 'nullable|string|max:300',
            'pregunta_neutral' => 'nullable|string|max:300',
            'pregunta_negativa' => 'nullable|string|max:300',
        ];
    }
}
