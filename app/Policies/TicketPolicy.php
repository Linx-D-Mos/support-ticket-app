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
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');
        return ($user->rol_id === $adminRoleId)
            || ($user->rol_id === $agentRoleId && ($ticket->status === Status::OPEN || $ticket->agent_id === $user->id))
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
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');

        return ($ticket->user_id === $user->id)
            || ($user->rol_id === $adminRoleId)
            || ($user->rol_id === $agentRoleId && ($ticket->status === Status::OPEN || $ticket->agent_id === $user->id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ticket $ticket): bool
    {
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        return $user->rol_id === $adminRoleId;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ticket $ticket): bool
    {
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        return $user->rol_id === $adminRoleId;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ticket $ticket): bool
    {
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        return $user->rol_id === $adminRoleId;
    }
    public function addAgent(User $user, ticket $ticket): bool
    {
        $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
        $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');
        return ($user->rol_id === $adminRoleId ) 
        || ($user->rol_id === $agentRoleId);
    }
}
