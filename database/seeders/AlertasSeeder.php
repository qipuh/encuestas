<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertasSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['positiva', 'neutral', 'negativa'] as $emocion) {
            DB::table('alertas_config')->updateOrInsert(
                ['emocion' => $emocion],
                ['emocion' => $emocion, 'activa' => $emocion === 'negativa', 'destinatarios_ids' => json_encode([]), 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
