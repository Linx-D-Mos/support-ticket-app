<?php

namespace App\Listeners;

use App\Enums\RolEnum;
use App\Events\Answers\AnswerCreated;
use App\Models\User;
use App\Notifications\AnswerTicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

// use Illuminate\Queue\InteractsWithQueue;

class SendAnswerCreatedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AnswerCreated $event): void
    {
        //Lazy loadMissing carga de las relaciones por si acaso los datos del ticket cambian en mitad del camino
        $event->answer->loadMissing(['ticket.user', 'ticket.agent','user']);
          
        $recipient = null;

        if ($event->answer->ticket->user && $event->answer->ticket->user->id === $event->answer->user->id) {
            $recipient = $event->answer->ticket->agent;
        } else {
            $recipient = $event->answer->ticket->user;
        }
        if (! $recipient) {
            $recipient = User::whereHas(
                'rol',   
                fn($q) => $q->where('name', RolEnum::ADMIN)
            )
                ->inRandomOrder()
                ->first();
        }
        if ($recipient && $recipient->id !== $event->answer->user->id){
            Notification::send($recipient, new AnswerTicketNotification($event->answer));
        }
    }
}
