<?php

namespace App\Listeners;

use App\Events\TicketAgentReassigned;
use App\Notifications\TicketAssignAgentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTicketAgentReassigned implements ShouldQueue
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
    public function handle(TicketAgentReassigned $event): void
    {
        $recipients = collect();

        // Añadimos al agente si existe
        if ($event->ticket->agent_id) {
            $recipients->push($event->ticket->agent);
        }

        // También enviamos al dueño del ticket (cliente)
        $recipients->push($event->ticket->user);

        Notification::send($recipients, new TicketAssignAgentNotification($event->ticket));
    }
}
