<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            FuentesSeeder::class,
            CategoriasSeeder::class,
            AdminUserSeeder::class,
            ConfiguracionSeeder::class,
            AlertasSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
