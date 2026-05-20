<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEncuestaRequest;
use App\Http\Requests\UpdateEncuestaRequest;
use App\Models\Encuesta;
use Illuminate\Http\Request;

class EncuestaController extends Controller
{
    public function index(Request $request)
    {
        $q = Encuesta::with(['fuente:id,nombre', 'categoria:id,nombre', 'creadoPor:id,name'])
            ->withCount([
                'respuestas as positivas_count' => fn ($q) => $q->where('emocion', 'positiva'),
                'respuestas as neutrales_count' => fn ($q) => $q->where('emocion', 'neutral'),
                'respuestas as negativas_count' => fn ($q) => $q->where('emocion', 'negativa'),
            ])
            ->where('estado', '!=', 'eliminada');

        if ($request->fuente_id) {
            $q->where('fuente_id', $request->fuente_id);
        }
        if ($request->categoria_id) {
            $q->where('categoria_id', $request->categoria_id);
        }
        if ($request->estado) {
            $q->where('estado', $request->estado);
        }
        if ($request->search) {
            $q->where('nombre', 'like', "%{$request->search}%");
        }

        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = ['id', 'nombre', 'created_at', 'estado'];
        $q->orderBy(in_array($sort, $allowed) ? $sort : 'created_at', $dir);

        return response()->json($q->paginate((int) ($request->per_page ?? 15)));
    }

    public function store(StoreEncuestaRequest $request)
    {
        $encuesta = Encuesta::create(array_merge($request->validated(), [
            'creado_por' => $request->user()->id,
            'estado' => 'activa',
        ]));

        return response()->json($encuesta->load(['fuente:id,nombre', 'categoria:id,nombre']), 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Encuesta::with(['fuente:id,nombre', 'categoria:id,nombre', 'creadoPor:id,name'])
                ->findOrFail($id)
        );
    }

    public function update(UpdateEncuestaRequest $request, string $id)
    {
        $encuesta = Encuesta::findOrFail($id);
        $encuesta->update($request->validated());

        return response()->json($encuesta->load(['fuente:id,nombre', 'categoria:id,nombre']));
    }

    public function destroy(string $id)
    {
        Encuesta::findOrFail($id)->update(['estado' => 'eliminada']);

        return response()->json(['message' => 'Encuesta eliminada']);
    }

    public function togglePause(Encuesta $encuesta)
    {
        $encuesta->update([
            'estado' => $encuesta->estado === 'activa' ? 'pausada' : 'activa',
        ]);

        return response()->json($encuesta);
    }

    public function activaParaFuente(Request $request)
    {
        $user = $request->user()->load('fuentes');
        $fuenteIds = $user->fuentes->pluck('id');

        $encuesta = Encuesta::with(['fuente:id,nombre', 'categoria:id,nombre'])
            ->whereIn('fuente_id', $fuenteIds)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if (! $encuesta) {
            return response()->json(['message' => 'No hay encuesta activa para tu sede'], 404);
        }

        return response()->json($encuesta);
    }
}
