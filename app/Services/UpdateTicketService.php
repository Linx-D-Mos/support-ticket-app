<?php

namespace App\Services;

use App\DTOs\UpdateTicketDTO;
use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Label;
use App\Models\Ticket;
use App\Models\User;
use Exception;

class UpdateTicketService 
{
    public function updateTicket(UpdateTicketDTO $dto){
        $ticket = Ticket::find($dto->ticket_id);
        $user = $dto->user_id instanceof User ? $dto->user_id : User::find($dto->user_id);

        if(! $this->validateTicket($ticket, $user)){
            throw new Exception('No puedes editar este ticket, solo está permitido hasta 10 min después de crearlo');
        }
        if($user->hasRole(RolEnum::CUSTOMER)){
            $ticket->update([
                'title' => $dto->title,
                'priority' => $dto->priority,
            ]);
        }else{
            $ticket->update([
                'priority' => $dto->priority,
            ]);
            if($dto->labels){
                $labelIds = Label::whereIn('name', $dto->labels)->pluck('id');
                $ticket->labels()->sync($labelIds);
            }
        }
        return $ticket->load('labels', 'user', 'agent');
    }   
    public function validateTicket(Ticket $ticket, User $user){
        // Allow agents to bypass the 10-minute rule
        if (!$user->hasRole(RolEnum::CUSTOMER)) {
            return true;
        }
        return ($ticket->hasStatus(Status::OPEN) && ($ticket->created_at->diffInMinutes(now())) <= 10) ;
    }
}
