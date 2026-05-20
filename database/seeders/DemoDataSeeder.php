<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $roles   = DB::table('roles')->pluck('id', 'nombre');
        $fuentes = DB::table('fuentes')->pluck('id', 'nombre');

        // ── Usuarios demo ───────────────────────────────────────────
        $usuarios = [
            [
                'name'      => 'Laura',
                'apellidos' => 'Quispe',
                'email'     => 'supervisor@emotix.pe',
                'password'  => Hash::make('password'),
                'role_id'   => $roles['Supervisor'],
                'habilitado'=> true,
                'fuente'    => 'Procabell Surco',
            ],
            [
                'name'      => 'Marco',
                'apellidos' => 'Torres',
                'email'     => 'calidad@emotix.pe',
                'password'  => Hash::make('password'),
                'role_id'   => $roles['Calidad'],
                'habilitado'=> true,
                'fuente'    => 'Procabell Jesús María',
            ],
            [
                'name'      => 'Ana',
                'apellidos' => 'Flores',
                'email'     => 'fuente@emotix.pe',
                'password'  => Hash::make('password'),
                'role_id'   => $roles['Fuente'],
                'habilitado'=> true,
                'fuente'    => 'Procabell Los Olivos',
            ],
        ];

        foreach ($usuarios as $u) {
            $fuente = $u['fuente'];
            unset($u['fuente']);

            $u['created_at'] = now();
            $u['updated_at'] = now();

            DB::table('users')->updateOrInsert(['email' => $u['email']], $u);
            $userId = DB::table('users')->where('email', $u['email'])->value('id');

            if ($userId && isset($fuentes[$fuente])) {
                DB::table('user_fuentes')->updateOrInsert(
                    ['user_id' => $userId, 'fuente_id' => $fuentes[$fuente]]
                );
            }
        }

        // ── Encuestas activas demo ──────────────────────────────────
        $adminId = DB::table('users')->where('email', 'admin@emotix.pe')->value('id');

        $encuestas = [
            [
                'nombre'             => 'Satisfacción General - Surco',
                'fuente_id'          => $fuentes['Procabell Surco'],
                'pregunta_principal' => '¿Cómo calificarías tu atención hoy?',
                'pregunta_positiva'  => '¿Qué fue lo que más te gustó?',
                'pregunta_neutral'   => '¿Qué podríamos mejorar?',
                'pregunta_negativa'  => 'Cuéntanos qué salió mal para poder mejorar.',
                'estado'             => 'activa',
                'creado_por'         => $adminId,
            ],
            [
                'nombre'             => 'Satisfacción General - Jesús María',
                'fuente_id'          => $fuentes['Procabell Jesús María'],
                'pregunta_principal' => '¿Cómo fue tu experiencia con nosotros hoy?',
                'pregunta_positiva'  => '¡Nos alegra! ¿Qué destacarías?',
                'pregunta_neutral'   => '¿Qué podemos hacer mejor?',
                'pregunta_negativa'  => 'Lo sentimos. ¿Qué ocurrió?',
                'estado'             => 'activa',
                'creado_por'         => $adminId,
            ],
            [
                'nombre'             => 'Satisfacción General - Los Olivos',
                'fuente_id'          => $fuentes['Procabell Los Olivos'],
                'pregunta_principal' => '¿Cómo calificarías el servicio recibido?',
                'pregunta_positiva'  => '¿Qué fue lo mejor de tu visita?',
                'pregunta_neutral'   => '¿Algo que mejoraríamos?',
                'pregunta_negativa'  => 'Queremos mejorar. ¿Qué falló?',
                'estado'             => 'activa',
                'creado_por'         => $adminId,
            ],
        ];

        foreach ($encuestas as $e) {
            $e['created_at'] = now();
            $e['updated_at'] = now();
            DB::table('encuestas')->updateOrInsert(
                ['nombre' => $e['nombre'], 'fuente_id' => $e['fuente_id']],
                $e
            );
        }

        // ── Respuestas y comentarios demo (últimos 7 días) ──────────
        $encuestasIds = DB::table('encuestas')->pluck('id', 'fuente_id');
        $emociones    = ['positiva', 'positiva', 'positiva', 'neutral', 'negativa'];
        $comentarios  = [
            'positiva' => ['Excelente atención, muy amables.', 'Rápido y eficiente.', 'Todo perfecto.', null],
            'neutral'  => ['El tiempo de espera fue largo.', 'Podría mejorar la señalización.', null],
            'negativa' => ['Tuve que esperar más de 30 minutos.', 'El personal no fue amable.'],
        ];

        $adminUserId = $adminId;

        for ($dia = 6; $dia >= 0; $dia--) {
            $fecha = now()->subDays($dia);

            foreach ($encuestasIds as $fuenteId => $encuestaId) {
                $cantidad = rand(8, 20);
                for ($i = 0; $i < $cantidad; $i++) {
                    $emocion = $emociones[array_rand($emociones)];
                    $textos  = $comentarios[$emocion];
                    $texto   = $textos[array_rand($textos)];

                    $respuestaId = DB::table('respuestas_encuesta')->insertGetId([
                        'encuesta_id' => $encuestaId,
                        'usuario_id'  => $adminUserId,
                        'emocion'     => $emocion,
                        'comentario'  => $texto,
                        'fecha_hora'  => $fecha->copy()->addMinutes(rand(0, 480)),
                    ]);

                    if ($texto) {
                        DB::table('comentarios')->insert([
                            'fuente_id'   => $fuenteId,
                            'encuesta_id' => $encuestaId,
                            'respuesta_id'=> $respuestaId,
                            'comentario'  => $texto,
                            'emocion'     => $emocion,
                            'estado'      => ['pendiente', 'en_proceso', 'resuelto'][rand(0, 2)],
                            'fecha'       => $fecha->toDateString(),
                            'hora'        => $fecha->copy()->addMinutes(rand(0, 480))->toTimeString(),
                            'created_at'  => $fecha,
                            'updated_at'  => $fecha,
                        ]);
                    }
                }
            }
        }
    }
}
