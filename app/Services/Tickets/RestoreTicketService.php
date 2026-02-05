<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use Exception;

Class RestoreTicketService{
    public function restoreTicket(String $id){
        $ticket = Ticket::onlyTrashed()->find($id);
        if(! $ticket){
            throw new Exception('No se puede realizar esta acción.'); 
        }
        $ticket->restore();
        return $ticket;
    }
}
