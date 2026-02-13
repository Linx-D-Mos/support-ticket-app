<?php

namespace App\Listeners;

use App\Enums\RolEnum;
use App\Events\TicketCreated;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendTicketCreateNotification implements ShouldQueue
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
    public function handle(TicketCreated $event): void
    {
        // Traemos a todos los admins y agentes
        //
        $event->ticket->loadMissing('user','agent','files','answers','labels');
        $recipients = User::whereHas(
            'rol', fn($q) => $q->
            whereIn('name', [RolEnum::ADMIN, RolEnum::AGENT]))
            ->get();

        Notification::send($recipients, new NewTicketNotification($event->ticket));
    }
}
