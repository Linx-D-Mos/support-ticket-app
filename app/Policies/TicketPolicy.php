<?php

namespace App\Policies;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ticket $ticket): Response
    {
        if (!$this->canAccessTicket($user, $ticket)) {
            return Response::deny('No tienes autorización para acceder a esta funcionalidad');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ticket $ticket): Response
    {
        if ($ticket->hasStatus(Status::CLOSED)) {
            return Response::deny('No puedes modificar un ticket que ya está cerrado.');
        }

        if ($user->id === $ticket->user_id && !$ticket->isEditableInTimeWindow(10)) {
            return Response::deny('El tiempo para editar tu ticket ha expirado (máximo 10 minutos).');
        }

        if (!$this->canAccessTicket($user, $ticket)) {
            return Response::deny('No tienes permiso para editar este ticket.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): Response
    {
        if ($user->hasRole(RolEnum::ADMIN)) {
            return Response::allow();
        }

        if ($user->id === $ticket->user_id && $ticket->isEditableInTimeWindow(10)) {
            return Response::allow();
        }

        return Response::deny('No tienes permiso para eliminar este ticket o el tiempo límite ha expirado.');
    }
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): Response
    {
        if (!($user->hasRole(RolEnum::ADMIN))) {
            return Response::deny('No tienes autorización para acceder a esta funcionalidad');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function addAgent(User $user, ticket $ticket): Response
    {
        if (!(($user->hasRole(RolEnum::ADMIN))
            || ($ticket->agent_id === null && $user->hasRole(RolEnum::AGENT)))) {
            return Response::deny('No tienes autorización para acceder a esta funcionalidad');
        }
        return Response::allow();
    }
    public function resolve(User $user, ticket $ticket): Response
    {
        // No se puede resolver algo que ya terminó su ciclo
        if ($ticket->hasStatus(Status::RESOLVED) ) {
            return Response::deny('Este ticket ya se encuentra resuelto.');
        }
        if($ticket->hasStatus(Status::CLOSED)){
            return Response::deny('Este ticket se encuentra cerrado.');
        }

        // Solo el agente asignado o el administrador pueden resolver
        if (!(($ticket->agent_id === $user->id) || ($user->hasRole(RolEnum::ADMIN)))) {
            return Response::deny('No tienes autorización para acceder a esta funcionalidad');
        }

        return Response::allow();
    }
    public function close(User $user, ticket $ticket): Response
    {
        if ($ticket->hasStatus(Status::CLOSED)) {
            return Response::deny('Este ticket ya se encuentra cerrado.');
        }

        if (!$ticket->hasStatus(Status::RESOLVED)) {
            return Response::deny('Solo puedes cerrar tickets que hayan sido marcados como resueltos previamente.');
        }
        if ($user->hasRole(RolEnum::ADMIN) || $ticket->user_id === $user->id) {
            return Response::allow();
        }
        return Response::deny('No tienes autorización para acceder a esta funcionalidad');
    }
    public function assign(User $user, ticket $ticket): Response
    {
        if (! (($ticket->agent_id === $user->id) || ($user->hasRole(RolEnum::ADMIN)))) {
            return Response::deny('No tienes autorización para acceder a esta funcionalidad');
        }
        return Response::allow();
    }
    private function canAccessTicket(User $user, ticket $ticket): bool
    {
        return ($ticket->user_id === $user->id)
            || $user->hasRole(RolEnum::ADMIN)
            || ($ticket->agent_id === $user->id)
            || ($ticket->hasStatus(Status::OPEN) && $user->hasRole(RolEnum::AGENT));
    }
}
