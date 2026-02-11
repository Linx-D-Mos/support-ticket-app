<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\RolEnum;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí registramos los canales. El array ['guards' => ['sanctum']] es VITAL
| para que Laravel sepa leer el token Bearer que envía el frontend.
|
*/

// Canal privado para el Usuario (su propio ID)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admins', function ($user) {
    // Verificamos el rol y casteamos a booleano explícito
    return (bool) $user->hasRole(RolEnum::ADMIN);
}, ['guards' => ['sanctum']]);

Broadcast::channel('customers', function($user){
    return (bool) $user->hasRole(RolEnum::CUSTOMER);
}, ['guards' => ['sanctum']]);