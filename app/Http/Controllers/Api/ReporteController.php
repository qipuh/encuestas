<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Encuesta;
use App\Models\Fuente;
use App\Models\RespuestaEncuesta;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function kpiPublico()
    {
        $total = RespuestaEncuesta::count() ?: 1;
        $positivas = RespuestaEncuesta::where('emocion', 'positiva')->count();
        $neutrales = RespuestaEncuesta::where('emocion', 'neutral')->count();
        $satisfaccion = round((($positivas + $neutrales * 0.5) / $total) * 5, 1);

        return response()->json(['value' => $satisfaccion, 'trend' => 8.6]);
    }

    public function dashboard(Request $request)
    {
        $desde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('fecha_hasta', now()->toDateString());

        $base = RespuestaEncuesta::whereBetween('fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59']);

        if ($request->fuente_id) {
            $base->whereHas('encuesta', fn ($q) => $q->where('fuente_id', $request->fuente_id));
        }

        $positivas = (clone $base)->where('emocion', 'positiva')->count();
        $neutrales = (clone $base)->where('emocion', 'neutral')->count();
        $negativas = (clone $base)->where('emocion', 'negativa')->count();
        $total = max($positivas + $neutrales + $negativas, 1);
        $satisfaccion = round((($positivas + $neutrales * 0.5) / $total) * 100);

        // Satisfacción diaria
        $porDia = (clone $base)
            ->selectRaw('DATE(fecha_hora) as fecha,
                SUM(emocion="positiva") as pos,
                SUM(emocion="neutral") as neu,
                COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => [
                'fecha' => $r->fecha,
                'satisfaccion' => $r->total > 0 ? round((($r->pos + $r->neu * 0.5) / $r->total) * 100) : 0,
            ]);

        // Por fuente
        $porFuente = Fuente::withCount([
            'respuestas as total' => fn ($q) => $q->whereBetween('fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59']),
            'respuestas as positivas' => fn ($q) => $q->where('emocion', 'positiva')->whereBetween('fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59']),
            'respuestas as neutrales' => fn ($q) => $q->where('emocion', 'neutral')->whereBetween('fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59']),
            'respuestas as negativas' => fn ($q) => $q->where('emocion', 'negativa')->whereBetween('fecha_hora', [$desde.' 00:00:00', $hasta.' 23:59:59']),
        ])
            ->having('total', '>', 0)
            ->get()
            ->map(fn ($f) => [
                'fuente' => $f->nombre,
                'total' => $f->total,
                'positivas' => $f->positivas,
                'neutrales' => $f->neutrales,
                'negativas' => $f->negativas,
                'satisfaccion' => $f->total > 0 ? round((($f->positivas + $f->neutrales * 0.5) / $f->total) * 100) : 0,
            ]);

        return response()->json([
            'total_respuestas' => $total,
            'satisfaccion' => $satisfaccion,
            'positivas' => $positivas,
            'neutrales' => $neutrales,
            'negativas' => $negativas,
            'pendientes' => Comentario::where('estado', 'pendiente')->count(),
            'encuestas_activas' => Encuesta::where('estado', 'activa')->count(),
            'satisfaccion_diaria' => $porDia,
            'por_fuente' => $porFuente,
            // Legacy dashboard fields
            'encuestas_dia' => RespuestaEncuesta::whereDate('fecha_hora', today())->count(),
            'comentarios_dia' => Comentario::whereDate('created_at', today())->count(),
            'total_emociones' => $total,
            'satisfaccion_chart' => $porDia->pluck('satisfaccion'),
        ]);
    }

    public function satisfaccionDiaria(Request $request)
    {
        $desde = $request->input('fecha_desde', now()->subDays(29)->toDateString());
        $hasta = $request->input('fecha_hasta', now()->toDateString());

        $datos = RespuestaEncuesta::whereBetween('fecha_hora', [$desde, $hasta])
            ->selectRaw('DATE(fecha_hora) as fecha,
                SUM(emocion="positiva") as pos,
                SUM(emocion="neutral") as neu,
                COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => [
                'fecha' => $r->fecha,
                'satisfaccion' => $r->total > 0 ? round((($r->pos + $r->neu * 0.5) / $r->total) * 100) : 0,
            ]);

        return response()->json($datos);
    }
}
