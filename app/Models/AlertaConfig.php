<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertaConfig extends Model
{
    protected $table = 'alertas_config';

    protected $fillable = ['emocion', 'activa', 'destinatarios_ids'];

    protected $casts = ['destinatarios_ids' => 'array', 'activa' => 'boolean'];
}
