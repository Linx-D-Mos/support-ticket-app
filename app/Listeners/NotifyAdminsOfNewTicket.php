<?php

namespace App\Listeners;

use App\Enums\RolEnum;
use App\Events\TicketCreated;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAdminsOfNewTicket implements ShouldQueue
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
        //Traemos a todos los admins
        $admins = User::whereHas('rol', fn($q)
        => $q->where('name', RolEnum::ADMIN))->get();

        Notification::send($admins, new NewTicketNotification($event->ticket));
    }
}
