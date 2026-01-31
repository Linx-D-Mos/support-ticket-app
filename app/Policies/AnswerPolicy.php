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
    public function create(User $user, Ticket $ticket): bool
    {
        return ($user->id === $ticket->user_id)
        || ($user->hasRole(RolEnum::AGENT) && ($user->id === $ticket->agent_id))
        || ($user->hasRole(RolEnum::ADMIN));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Answer $answer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Answer $answer): bool
    {
        return false;
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
