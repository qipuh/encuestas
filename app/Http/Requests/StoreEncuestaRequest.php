<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:160',
            'fuente_id' => 'required|exists:fuentes,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'pregunta_principal' => 'required|string|max:500',
            'pregunta_positiva' => 'nullable|string|max:300',
            'pregunta_neutral' => 'nullable|string|max:300',
            'pregunta_negativa' => 'nullable|string|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la encuesta es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 160 caracteres.',
            'fuente_id.required' => 'Debes seleccionar una fuente.',
            'fuente_id.exists' => 'La fuente seleccionada no existe.',
            'pregunta_principal.required' => 'La pregunta principal es obligatoria.',
            'pregunta_principal.max' => 'La pregunta no puede superar 500 caracteres.',
        ];
    }
}
