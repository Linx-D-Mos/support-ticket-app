<?php

namespace App\Policies;

use App\Enums\RolEnum;
use App\Models\Answer;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnswerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Answer $answer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Ticket $ticket): Response
    {

        if (
            !(($user->id === $ticket->user_id)
                || ($user->hasRole(RolEnum::AGENT) && ($user->id === $ticket->agent_id))
                || ($user->hasRole(RolEnum::ADMIN)))
        ) {
            return Response::deny('No tiene autorización para responder a este ticket');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Answer $answer): Response
    {
        // 1. Verificar si es dueño
        if ($user->id !== $answer->user_id) {
            return Response::deny('No eres el propietario de esta respuesta.');
        }

        // 2. Verificar tiempo (Usando nuestro Trait)
        if (! $answer->isEditableInTimeWindow(10)) {
            return Response::deny('El tiempo para editar ha expirado (máximo 10 minutos).');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Answer $answer): Response
    {
        if ($user->id !== $answer->user_id) {
            return Response::deny('No eres el propietario.');
        }

        if (! $answer->isEditableInTimeWindow(10)) {
            return Response::deny('Ya no puedes eliminar esto, el tiempo ha expirado.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Answer $answer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Answer $answer): bool
    {
        return false;
    }
}
