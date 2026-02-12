<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\RolEnum;
use App\Models\Ticket;
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

Broadcast::channel('customers', function ($user) {
    return (bool) $user->hasRole(RolEnum::CUSTOMER);
}, ['guards' => ['sanctum']]);

Broadcast::channel('ticket.{ticket}', function ($user, Ticket $ticket) {
    $isOwner = (int) $user->id === (int) $ticket->user_id;
    $isAssignedAgent = (int) $user->id === (int) $ticket->agent_id;
    $isAdmin = $user->isAdmin();

    return $isOwner || $isAssignedAgent || $isAdmin;
}, ['guards' => ['sanctum']]);

Broadcast::channel('ticket.{ticket}', function ($user, Ticket $ticket) {
    $isAdmin = $user->isAdmin();
    $isOwner = (int) $user->id === (int) $ticket->user_id;
    $isAgent = (int) $user->id === (int) $ticket->agent_id;

    if ($isAdmin || $isOwner || $isAgent) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'rol' => $user->rol->name
        ];
    }
    return false;
}, ['guards' => ['sanctum']]);
