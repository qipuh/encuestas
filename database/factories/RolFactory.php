<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Administrador', 'Supervisor', 'Calidad', 'Fuente',
            ]),
        ];
    }

    public function administrador(): static
    {
        return $this->state(['nombre' => 'Administrador']);
    }

    public function supervisor(): static
    {
        return $this->state(['nombre' => 'Supervisor']);
    }
}
