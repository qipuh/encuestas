<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $fuenteId = DB::table('fuentes')->where('nombre', 'Procabell Jesús María')->value('id');

        $userId = DB::table('users')->updateOrInsert(
            ['email' => 'admin@emotix.pe'],
            [
                'name'       => 'Carlos',
                'apellidos'  => 'Velásquez',
                'email'      => 'admin@emotix.pe',
                'password'   => Hash::make('password'),
                'role_id'    => $adminRoleId,
                'habilitado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $user = DB::table('users')->where('email', 'admin@emotix.pe')->first();
        if ($user && $fuenteId) {
            DB::table('user_fuentes')->updateOrInsert(
                ['user_id' => $user->id, 'fuente_id' => $fuenteId]
            );
        }
    }
}
