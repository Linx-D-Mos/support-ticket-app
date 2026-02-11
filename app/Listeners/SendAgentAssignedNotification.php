<?php

namespace App\Listeners;

use App\Events\TicketAddAgent;
use App\Notifications\TicketAddedAgentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendAgentAssignedNotification implements ShouldQueue
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
    public function handle(TicketAddAgent $event): void
    {
        $customer = $event->ticket->user;
        if($customer){
            Notification::send($customer, new TicketAddedAgentNotification($event->ticket));
        }
    }
}
