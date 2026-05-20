<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuente_id', 'encuesta_id', 'respuesta_id', 'comentario',
        'emocion', 'estado', 'fecha', 'hora',
        'cliente_nombre', 'cliente_dni', 'cliente_telefono',
    ];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class);
    }
}
