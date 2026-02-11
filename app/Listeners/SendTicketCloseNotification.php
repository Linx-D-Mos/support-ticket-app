<?php

namespace App\Listeners;

use App\Events\TicketClose;
use App\Notifications\TicketCloseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTicketCloseNotification
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
    public function handle(TicketClose $event): void
    {
        $event->ticket->loadMissing('user','agent');
        $recipients = collect([$event->ticket->user]);
        if($event->user->isAdmin()){
            $recipients->push($event->ticket->agent());
        }
        $recipients = $recipients->filter();

        Notification::send($recipients, new TicketCloseNotification($event->ticket, $event->user));
    }
}
