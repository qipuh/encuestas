<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre', 'fuente_id', 'categoria_id', 'creado_por',
        'pregunta_principal', 'pregunta_positiva', 'pregunta_neutral', 'pregunta_negativa', 'estado',
    ];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaEncuesta::class);
    }
}
