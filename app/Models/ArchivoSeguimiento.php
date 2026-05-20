<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoSeguimiento extends Model
{
    protected $table = 'archivos_seguimiento';

    protected $fillable = ['seguimiento_id', 'ruta_archivo', 'nombre_original'];

    public function seguimiento()
    {
        return $this->belongsTo(Seguimiento::class);
    }
}
