<?php

namespace App\Listeners;

use App\Events\TicketEscalated;
use App\Mail\TicketEscalatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEscalationNotification implements ShouldQueue
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
    public function handle(TicketEscalated $event): void
    {
        Mail::to('admin@soporte.com')->send(new TicketEscalatedMail($event->ticket));
    }
}
