<?php

namespace Database\Factories;

use App\Models\Comentario;
use App\Models\Encuesta;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComentarioFactory extends Factory
{
    protected $model = Comentario::class;

    public function definition(): array
    {
        $encuesta = Encuesta::factory()->create();

        return [
            'fuente_id' => $encuesta->fuente_id,
            'encuesta_id' => $encuesta->id,
            'respuesta_id' => null,
            'comentario' => $this->faker->sentence(12),
            'emocion' => $this->faker->randomElement(['positiva', 'neutral', 'negativa']),
            'estado' => 'pendiente',
            'fecha' => $this->faker->dateTimeBetween('-30 days')->format('Y-m-d'),
            'hora' => $this->faker->time('H:i:s'),
            'cliente_nombre' => $this->faker->name(),
            'cliente_dni' => null,
            'cliente_telefono' => null,
        ];
    }

    public function positiva(): static
    {
        return $this->state(['emocion' => 'positiva']);
    }

    public function negativa(): static
    {
        return $this->state(['emocion' => 'negativa']);
    }

    public function resuelto(): static
    {
        return $this->state(['estado' => 'resuelto']);
    }
}
