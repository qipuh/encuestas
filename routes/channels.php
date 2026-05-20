<?php

use Illuminate\Support\Facades\Broadcast;

// Canal de notificaciones por usuario — solo el propio usuario puede suscribirse
Broadcast::channel('notificaciones.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal genérico de usuario (Notifications de Laravel)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
