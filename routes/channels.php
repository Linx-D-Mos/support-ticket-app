<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Ticket;

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
//Osea las personas reciben las notificaciones que van dirigidas a ella por este canal
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('admins', function ($user){
    return $user->isAdmin();
});
Broadcast::channel('customers', function ($user) {
    return (bool) $user->isCustomer();
}, ['guards' => ['sanctum']]);

Broadcast::channel('agent', function ($user){
    return (bool) $user->isAgent();
}, ['guardas' => ['sanctum']]);

Broadcast::channel('admin-agent', function ($user) {
    // Verificamos el rol y casteamos a booleano explícito;
    return  $user->isAdmin() || $user->isAgent();
}, ['guards' => ['sanctum']]);

Broadcast::channel('tickets', function($user){
    $isAdmin = $user->isAdmin();
    $isAgent = $user->isAgent();
    $isCustomer = $user->isCustomer();
    return $isAdmin || $isAgent || $isCustomer;
}, ['guards' => ['sanctum']]);

//Canal de presencia y para mensajes 
Broadcast::channel('ticket.{ticket}', function ($user, Ticket $ticket) {
    $isOwner = (int) $user->id === (int) $ticket->user_id;
    $isAssignedAgent = (int) $user->id === (int) $ticket->agent_id;
    $isAgent = $ticket->hasStatus(Status::OPEN) && $user->isAgent();
    $isAdmin = $user->isAdmin();
    if ($isAdmin || $isOwner || $isAgent || $isAssignedAgent) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'rol' => $user->rol->name
        ];
    }
    return false;
}, ['guards' => ['sanctum']]);

// Broadcast::channel('ticket.{ticket}', function ($user, Ticket $ticket) {
//     $isAdmin = $user->isAdmin();
//     $isOwner = (int) $user->id === (int) $ticket->user_id;
//     $isAgent = (int) $user->id === (int) $ticket->agent_id;

//     if ($isAdmin || $isOwner || $isAgent) {
//         return [
//             'id' => $user->id,
//             'name' => $user->name,
//             'rol' => $user->rol->name
//         ];
//     }
//     return false;
// }, ['guards' => ['sanctum']]);
