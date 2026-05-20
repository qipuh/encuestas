<?php

namespace Database\Factories;

use App\Models\Fuente;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuenteFactory extends Factory
{
    protected $model = Fuente::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->company(),
            'direccion' => $this->faker->streetAddress(),
            'distrito' => $this->faker->city(),
            'activa' => true,
        ];
    }
}
