<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fuente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'direccion', 'distrito', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_fuentes');
    }

    public function encuestas()
    {
        return $this->hasMany(Encuesta::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function respuestas()
    {
        return $this->hasManyThrough(RespuestaEncuesta::class, Encuesta::class);
    }
}
