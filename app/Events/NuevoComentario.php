<?php

namespace App\Events;

use App\Models\Comentario;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoComentario implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comentario $comentario) {}

    public function broadcastOn(): array
    {
        // Notificar a Admins, Supervisores y Calidad
        $roles = ['Administrador', 'Supervisor', 'Calidad'];

        $destinatarios = User::whereHas('rol', fn ($q) => $q->whereIn('nombre', $roles))
            ->where('habilitado', true)
            ->pluck('id');

        return $destinatarios->map(fn ($id) => new PrivateChannel("notificaciones.{$id}"))->all();
    }

    public function broadcastAs(): string
    {
        return 'nuevo-comentario';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comentario->id,
            'emocion' => $this->comentario->emocion,
            'comentario' => \Str::limit($this->comentario->comentario, 80),
            'fuente' => $this->comentario->fuente?->nombre,
            'fecha' => $this->comentario->fecha,
            'hora' => $this->comentario->hora,
        ];
    }
}
