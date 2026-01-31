<?php

namespace App\Policies;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Rol;
use App\Models\User;
use App\Models\ticket;
use App\Models\Ticket as ModelsTicket;

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
    public function view(User $user, ticket $ticket): bool
    {
        return ($user->hasRole(RolEnum::ADMIN))
            || ($user->hasRole(RolEnum::AGENT) && ($ticket->status === Status::OPEN || $ticket->agent_id === $user->id))
            || ($ticket->user_id === $user->id);
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
    public function update(User $user, ticket $ticket): bool
    {

        return ($ticket->user_id === $user->id)
            || ($user->hasRole(RolEnum::ADMIN))
            || ($user->hasRole(RolEnum::AGENT) && ($ticket->hasStatus(Status::OPEN) || $ticket->agent_id === $user->id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ticket $ticket): bool
    {
        return $user->hasRole(RolEnum::ADMIN);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ticket $ticket): bool
    {
        return $user->hasRole(RolEnum::ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ticket $ticket): bool
    {
        return $user->hasRole(RolEnum::ADMIN);
    }
    public function addAgent(User $user, ticket $ticket): bool
    {
        return ($user->hasRole(RolEnum::ADMIN))
            || ($ticket->agent_id === null && $user->hasRole(RolEnum::AGENT));
    }
    public function resolve(User $user, ticket $ticket): bool
    {
        return (!$ticket->hasStatus(Status::RESOLVED) && !$ticket->hasStatus(Status::CLOSED))
            && (($user->hasRole(RolEnum::AGENT)  && ($ticket->agent_id === $user->id)) || $user->hasRole(RolEnum::ADMIN));
    }
    public function close(User $user, ticket $ticket): bool
    {
        return (!$ticket->hasStatus(Status::CLOSED) && ($ticket->hasStatus(Status::OPEN)  || $ticket->hasStatus(Status::RESOLVED)))
            && (($user->hasRole(RolEnum::CUSTOMER) && ($ticket->user_id === $user->id)) || $user->hasRole(RolEnum::ADMIN));
    }
}
