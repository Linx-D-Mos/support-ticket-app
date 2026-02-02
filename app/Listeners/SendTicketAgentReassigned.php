<?php

namespace App\Listeners;

use App\Events\TicketAgentReassigned;
use App\Mail\TicketAgentReassigned as MailTicketAgentReassigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTicketAgentReassigned implements ShouldQueue
{
    use InteractsWithQueue;

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

        Mail::to($recipients)->send(new MailTicketAgentReassigned($event->ticket));
    }
}
