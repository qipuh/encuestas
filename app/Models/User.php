<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'apellidos', 'email', 'password', 'role_id',
        'telefono', 'dni', 'foto', 'ultima_actividad', 'habilitado',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultima_actividad' => 'datetime',
            'password' => 'hashed',
            'habilitado' => 'boolean',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

    public function fuentes()
    {
        return $this->belongsToMany(Fuente::class, 'user_fuentes');
    }
}
