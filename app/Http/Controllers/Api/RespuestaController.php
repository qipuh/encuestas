<?php

namespace App\Http\Controllers\Api;

use App\Events\NuevoComentario;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PushController;
use App\Http\Requests\StoreRespuestaRequest;
use App\Jobs\VerificarUmbralSatisfaccion;
use App\Models\Comentario;
use App\Models\Encuesta;
use App\Models\RespuestaEncuesta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RespuestaController extends Controller
{
    public function store(StoreRespuestaRequest $request)
    {
        $data = $request->validated();

        $encuesta = Encuesta::findOrFail($data['encuesta_id']);

        DB::beginTransaction();
        try {
            $respuesta = RespuestaEncuesta::create([
                'encuesta_id' => $data['encuesta_id'],
                'usuario_id' => $request->user()->id,
                'emocion' => $data['emocion'],
                'comentario' => $data['comentario'] ?? null,
                'fecha_hora' => $data['fecha_hora'] ?? now(),
            ]);

            // Crear comentario automáticamente si hay texto
            $comentario = null;
            if (! empty($data['comentario'])) {
                $comentario = Comentario::create([
                    'fuente_id' => $encuesta->fuente_id,
                    'encuesta_id' => $encuesta->id,
                    'respuesta_id' => $respuesta->id,
                    'comentario' => $data['comentario'],
                    'emocion' => $data['emocion'],
                    'estado' => 'pendiente',
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'cliente_nombre' => $data['cliente_nombre'] ?? null,
                    'cliente_dni' => $data['cliente_dni'] ?? null,
                    'cliente_telefono' => $data['cliente_telefono'] ?? null,
                ]);

                // Broadcast en tiempo real
                event(new NuevoComentario($comentario->load('fuente')));

                // Verificar umbral de satisfacción en background
                VerificarUmbralSatisfaccion::dispatch($encuesta->fuente_id)->onQueue('default');

                // Push notification a usuarios habilitados
                $userIds = User::where('habilitado', true)->pluck('id')->toArray();
                if ($userIds) {
                    $fuente = $comentario->fuente?->nombre ?? 'Emotix';
                    PushController::sendToUsers(
                        $userIds,
                        'Nuevo comentario — ' . $fuente,
                        $comentario->comentario,
                        '/app/comentarios'
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'respuesta_id' => $respuesta->id,
            'offline_id' => $data['offline_id'] ?? null, // devolver para reconciliar en frontend
            'mensaje' => '¡Gracias por tu respuesta!',
        ], 201);
    }

    // Endpoint para sincronizar respuestas offline en batch
    public function syncOffline(Request $request)
    {
        $request->validate([
            'respuestas' => 'required|array|max:100',
            'respuestas.*' => 'array',
        ]);

        $resultados = [];
        foreach ($request->respuestas as $item) {
            try {
                $res = $this->store(new Request($item + ['_user' => $request->user()]));
                $resultados[] = ['offline_id' => $item['offline_id'] ?? null, 'status' => 'ok'];
            } catch (\Throwable $e) {
                $resultados[] = ['offline_id' => $item['offline_id'] ?? null, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return response()->json(['sincronizadas' => count(array_filter($resultados, fn ($r) => $r['status'] === 'ok')), 'detalle' => $resultados]);
    }
}
