<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    protected $fillable = ['comentario_id', 'user_id', 'nota', 'accion'];

    public function comentario()
    {
        return $this->belongsTo(Comentario::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoSeguimiento::class);
    }
}
