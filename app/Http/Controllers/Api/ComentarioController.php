<?php

namespace App\Http\Controllers\Api;

use App\Exports\ComentariosExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateComentarioRequest;
use App\Models\ArchivoSeguimiento;
use App\Models\Comentario;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ComentarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Comentario::with(['fuente', 'encuesta', 'seguimientos.usuario'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora');

        if ($request->fuente_id) {
            $query->where('fuente_id', $request->fuente_id);
        }
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->emocion) {
            $query->where('emocion', $request->emocion);
        }
        if ($request->fecha_desde) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->fecha_hasta) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }
        if ($request->search) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('comentario', 'like', "%$q%")
                    ->orWhere('cliente_nombre', 'like', "%$q%")
                    ->orWhere('cliente_dni', 'like', "%$q%");
            });
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show(Comentario $comentario)
    {
        $comentario->load(['fuente', 'encuesta', 'seguimientos.usuario', 'seguimientos.archivos']);

        return response()->json($comentario);
    }

    public function update(UpdateComentarioRequest $request, Comentario $comentario)
    {
        $comentario->update($request->validated());

        return response()->json($comentario);
    }

    public function agregarSeguimiento(Request $request, Comentario $comentario)
    {
        $data = $request->validate([
            'nota' => 'required|string',
            'accion' => 'nullable|in:pendiente,en_proceso,resuelto',
        ]);

        $seguimiento = $comentario->seguimientos()->create([
            'user_id' => $request->user()->id,
            'nota' => $data['nota'],
            'accion' => $data['accion'] ?? null,
        ]);

        if ($data['accion']) {
            $comentario->update(['estado' => $data['accion']]);
        }

        // Subir archivos adjuntos si vienen
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $ruta = $archivo->store('seguimientos', 'public');
                ArchivoSeguimiento::create([
                    'seguimiento_id' => $seguimiento->id,
                    'ruta_archivo' => $ruta,
                    'nombre_original' => $archivo->getClientOriginalName(),
                ]);
            }
        }

        $seguimiento->load(['usuario', 'archivos']);

        return response()->json($seguimiento, 201);
    }

    public function exportar(Request $request)
    {
        $filtros = $request->only(['fuente_id', 'estado', 'emocion', 'fecha_desde', 'fecha_hasta']);

        return Excel::download(new ComentariosExport($filtros), 'comentarios_'.now()->format('Y-m-d').'.xlsx');
    }
}
