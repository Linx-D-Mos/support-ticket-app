<?php

namespace App\Listeners;

use App\Enums\RolEnum;
use App\Events\TicketResolved;
use App\Notifications\TicketResolvedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTicketResolvedNotification
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
    public function handle(TicketResolved $event): void
    {
        $event->ticket->loadMissing('user','agent');
        $recipients = collect([$event->ticket->user]);
        if($event->user->isAdmin()){
            $recipients->push($event->ticket->agent);
        }
        $recipients = $recipients->filter();
        Notification::send($recipients,new TicketResolvedNotification($event->ticket, $event->user));
    }
}
