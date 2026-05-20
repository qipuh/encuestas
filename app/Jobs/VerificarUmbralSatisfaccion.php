<?php

namespace App\Jobs;

use App\Models\AlertaConfig;
use App\Models\ConfiguracionGeneral;
use App\Models\Fuente;
use App\Models\RespuestaEncuesta;
use App\Models\User;
use App\Notifications\AlertaSatisfaccionBaja;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerificarUmbralSatisfaccion implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $fuenteId) {}

    public function handle(): void
    {
        $config = ConfiguracionGeneral::first();
        $umbral = $config?->umbral_satisfaccion_bajo ?? 40;

        // Calcular satisfacción de las últimas 24h para esta fuente
        $desde = now()->subDay();
        $total = RespuestaEncuesta::whereHas('encuesta', fn ($q) => $q->where('fuente_id', $this->fuenteId))
            ->where('created_at', '>=', $desde)
            ->count();

        if ($total === 0) {
            return;
        }

        $positivas = RespuestaEncuesta::whereHas('encuesta', fn ($q) => $q->where('fuente_id', $this->fuenteId))
            ->where('created_at', '>=', $desde)
            ->where('emocion', 'positiva')
            ->count();

        $satisfaccion = round($positivas / $total * 100);

        if ($satisfaccion > $umbral) {
            return;
        }

        // Verificar si la alerta de negativa está activa
        $alertaActiva = AlertaConfig::where('emocion', 'negativa')
            ->where('activa', true)
            ->exists();

        if (! $alertaActiva) {
            return;
        }

        $fuente = Fuente::find($this->fuenteId);

        // Notificar a todos los admins/supervisores
        $destinatarios = User::whereHas('rol', fn ($q) => $q->whereIn('nombre', ['Administrador', 'Supervisor']))
            ->where('habilitado', true)
            ->get();

        foreach ($destinatarios as $usuario) {
            $usuario->notify(new AlertaSatisfaccionBaja($fuente, $satisfaccion, $umbral));
        }
    }
}
