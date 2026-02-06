<?php

namespace App\Services;

use App\Enums\RolEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class NotificationService
{
    public function sendNotification(Model $model, User $actor, Notification $notification)
    {
        $recipient = null;

        if ($actor->id === $model->user_id) {
            if ($model->agent_id) {
                $recipient = $model->agent ?: User::find($model->agent_id);
            }
        } else {
            $recipient = $model->user ?: User::find($model->user_id);
        }

        if (! $recipient) {
            $recipient = User::whereHas(
                'rol',
                fn($q) => $q->where('name', RolEnum::ADMIN)
            )->inRandomOrder()
                ->first();
        }

        if ($recipient && $recipient->id !== $actor->id) {
            $recipient->notify($notification);
        }
    }
}
