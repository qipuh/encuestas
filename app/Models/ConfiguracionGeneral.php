<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionGeneral extends Model
{
    protected $table = 'configuracion_general';

    protected $fillable = [
        'organizacion', 'pais', 'distrito', 'telefono', 'email_responsable',
        'cierre_sesion', 'hora_limite_login',
        'umbral_timeon_bajo', 'umbral_timeon_medio', 'umbral_timeon_alto',
        'umbral_satisfaccion', 'logo_path',
        // legacy columns kept in DB for backward compat
        'cierre_sesion_minutos', 'umbral_tiempo_atencion', 'umbral_satisfaccion_bajo',
    ];
}
