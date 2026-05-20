<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $cats = ['Pacientes', 'Personal Interno', 'Proveedores'];
        foreach ($cats as $c) {
            DB::table('categorias')->updateOrInsert(['nombre' => $c], ['nombre' => $c, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
