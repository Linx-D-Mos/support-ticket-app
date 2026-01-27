<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Events\Attribute\Listen;

class SendTicketCreatedEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    use InteractsWithQueue;
    public function __construct()
    {
        //
    }
    public $queue = 'emails';

    /**
     * Handle the event.
     */
    #[Listen]
    public function handle(TicketCreated $event): void
    {
        Log::info("Enviando email de bienvenida al Ticket ID: " . $event->ticket->id);
    }
}
