<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Supervisor',    'descripcion' => 'Gestión de encuestas y comentarios'],
            ['nombre' => 'Calidad',       'descripcion' => 'Seguimiento de comentarios y reportes'],
            ['nombre' => 'Fuente',        'descripcion' => 'Responder encuestas desde la sede'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(['nombre' => $rol['nombre']], $rol);
        }

        $modulos = ['dashboard', 'encuestas', 'comentarios', 'reportes', 'usuarios', 'configuracion'];
        $acciones = ['ver', 'crear', 'editar', 'eliminar'];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => "{$accion}_{$modulo}"],
                    ['name' => "{$accion}_{$modulo}", 'module' => $modulo]
                );
            }
        }

        // Admin tiene todos los permisos
        $adminId = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $permIds = DB::table('permissions')->pluck('id');
        foreach ($permIds as $pid) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $adminId, 'permission_id' => $pid]
            );
        }

        // Supervisor: ver+crear+editar encuestas, comentarios, reportes, dashboard
        $supervisorId = DB::table('roles')->where('nombre', 'Supervisor')->value('id');
        $supervisorPerms = DB::table('permissions')
            ->whereIn('module', ['dashboard', 'encuestas', 'comentarios', 'reportes'])
            ->whereIn('name', ['ver_dashboard', 'ver_encuestas', 'crear_encuestas', 'editar_encuestas',
                'ver_comentarios', 'editar_comentarios', 'ver_reportes'])
            ->pluck('id');
        foreach ($supervisorPerms as $pid) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $supervisorId, 'permission_id' => $pid]
            );
        }

        // Calidad: ver todo + editar comentarios
        $calidadId = DB::table('roles')->where('nombre', 'Calidad')->value('id');
        $calidadPerms = DB::table('permissions')
            ->where('name', 'LIKE', 'ver_%')
            ->orWhere('name', 'editar_comentarios')
            ->pluck('id');
        foreach ($calidadPerms as $pid) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $calidadId, 'permission_id' => $pid]
            );
        }
    }
}
