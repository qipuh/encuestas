<?php

namespace Database\Factories;

use App\Models\Encuesta;
use App\Models\Fuente;
use Illuminate\Database\Eloquent\Factories\Factory;

class EncuestaFactory extends Factory
{
    protected $model = Encuesta::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->sentence(4),
            'fuente_id' => Fuente::factory(),
            'categoria_id' => null,
            'creado_por' => null,
            'pregunta_principal' => '¿Cómo calificarías tu experiencia?',
            'pregunta_positiva' => '¿Qué fue lo que más te gustó?',
            'pregunta_neutral' => '¿Qué podríamos mejorar?',
            'pregunta_negativa' => '¿Cuál fue el principal problema?',
            'estado' => 'activa',
        ];
    }

    public function pausada(): static
    {
        return $this->state(['estado' => 'pausada']);
    }
}
