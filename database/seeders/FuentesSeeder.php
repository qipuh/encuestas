<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuentesSeeder extends Seeder
{
    public function run(): void
    {
        $fuentes = [
            ['nombre' => 'Procabell Surco',       'distrito' => 'Surco',       'activa' => true],
            ['nombre' => 'Procabell Jesús María',  'distrito' => 'Jesús María', 'activa' => true],
            ['nombre' => 'Procabell Los Olivos',   'distrito' => 'Los Olivos',  'activa' => true],
            ['nombre' => 'Procabell Miraflores',   'distrito' => 'Miraflores',  'activa' => false],
        ];

        foreach ($fuentes as $f) {
            DB::table('fuentes')->updateOrInsert(['nombre' => $f['nombre']], array_merge($f, [
                'created_at' => now(), 'updated_at' => now()
            ]));
        }
    }
}
