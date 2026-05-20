<?php

namespace App\Notifications;

use App\Models\Fuente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertaSatisfaccionBaja extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?Fuente $fuente,
        public readonly int $satisfaccion,
        public readonly int $umbral,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nombreFuente = $this->fuente?->nombre ?? 'Sin fuente';

        return (new MailMessage)
            ->subject("⚠️ Alerta: Satisfacción baja en {$nombreFuente}")
            ->greeting("Hola, {$notifiable->name}")
            ->line("Se detectó satisfacción baja en **{$nombreFuente}**.")
            ->line("Satisfacción actual: **{$this->satisfaccion}%** (umbral configurado: {$this->umbral}%)")
            ->action('Ver comentarios', url('/app/comentarios'))
            ->line('Este mensaje fue generado automáticamente por Emotix.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'alerta_satisfaccion',
            'fuente_id' => $this->fuente?->id,
            'fuente_nombre' => $this->fuente?->nombre,
            'satisfaccion' => $this->satisfaccion,
            'umbral' => $this->umbral,
            'mensaje' => "Satisfacción baja ({$this->satisfaccion}%) en {$this->fuente?->nombre}",
        ];
    }
}
