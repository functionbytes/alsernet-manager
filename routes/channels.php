<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal personalizado para notificaciones de usuarios
// Formato: users.{id}
Broadcast::channel('users.{id}', function ($user, $id) {
    // Solo el usuario puede escuchar sus propias notificaciones
    return (int) $user->id === (int) $id;
});
