<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRespuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'encuesta_id' => 'required|exists:encuestas,id',
            'emocion' => 'required|in:positiva,neutral,negativa',
            'comentario' => 'nullable|string|max:1000',
            'cliente_nombre' => 'nullable|string|max:120',
            'cliente_dni' => 'nullable|string|max:15',
            'cliente_telefono' => 'nullable|string|max:20',
            'cliente_email' => 'nullable|email|max:120',
            'fecha_hora' => 'nullable|date',
            'offline_id' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'encuesta_id.required' => 'Debes indicar la encuesta.',
            'encuesta_id.exists' => 'La encuesta indicada no existe.',
            'emocion.required' => 'La emoción es obligatoria.',
            'emocion.in' => 'La emoción debe ser positiva, neutral o negativa.',
            'comentario.max' => 'El comentario no puede superar 1000 caracteres.',
        ];
    }
}
