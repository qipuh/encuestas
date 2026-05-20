<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configuracion_general')->updateOrInsert(
            ['id' => 1],
            [
                'organizacion'              => 'Procabell',
                'pais'                      => 'Perú',
                'distrito'                  => 'Lima',
                'email_responsable'         => 'admin@emotix.pe',
                'cierre_sesion_minutos'     => 120,
                'umbral_satisfaccion_bajo'  => 60,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]
        );
    }
}
