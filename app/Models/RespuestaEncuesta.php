<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestaEncuesta extends Model
{
    public $timestamps = false;

    protected $table = 'respuestas_encuesta';

    protected $fillable = ['encuesta_id', 'usuario_id', 'emocion', 'comentario', 'fecha_hora'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
