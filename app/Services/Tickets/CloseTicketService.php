<?php

namespace App\Services\Tickets;


use App\Enums\Status;
use App\Events\TicketClose;
use App\Events\TicketUpdated;
use App\Models\Ticket;
use App\Notifications\CloseTicketNotification;
use App\Models\User;
use Carbon\Carbon;
use Exception;

Class CloseTicketService{
    public function __construct(){}

    public function closeTicket(Ticket $ticket, User $user): Ticket{
        if(!$ticket->hasStatus(Status::RESOLVED)){
            throw new Exception('Este ticket todavía no ha sido solucionado');
        }
        $ticket->update([
            'status' => Status::CLOSED,
            'closed_at' => Carbon::now(),
        ]);
        $ticket->load(['user','agent','answers','labels']);
        TicketClose::dispatch($ticket, $user);
        TicketUpdated::dispatch($ticket);
        return $ticket->load('files', 'agent', 'user','labels');
    }
}