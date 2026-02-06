<?php

namespace App\Services\Tickets;


use App\Enums\Status;
use App\Models\Ticket;
use App\Notifications\CloseTicketNotification;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;

Class CloseTicketService{
    public function __construct(
        public NotificationService $notification,
    ){}

    public function closeTicket(Ticket $ticket, User $user): Ticket{
        if(!$ticket->hasStatus(Status::RESOLVED)){
            throw new Exception('Este ticket todavía no ha sido solucionado');
        }
        $ticket->update([
            'status' => Status::CLOSED,
            'closed_at' => Carbon::now(),
        ]);
        $this->notification->sendNotification($ticket,$user, new CloseTicketNotification($ticket,$user));
        return $ticket->load('files', 'agent', 'user','labels');
    }
}