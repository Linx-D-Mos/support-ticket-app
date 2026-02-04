<?php

namespace App\Services;

use App\DTOs\UpdateTicketDTO;
use App\Enums\RolEnum;
use App\Models\Label;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateTicketService 
{
    public function updateTicket(UpdateTicketDTO $dto){
        return DB::transaction(function () use ($dto){
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
        $this->sendNotification($ticket,$user);
        $service = App(FileService::class);
        $ticket = $service->storeFile($ticket,$dto->files);
        return $ticket->load('labels', 'user', 'agent');
        });

    }   
    public function validateTicket(Ticket $ticket, User $user){
        // Allow agents to bypass the 10-minute rule
        if (!$user->hasRole(RolEnum::CUSTOMER)) {
            return true;
        }
        return ($ticket->created_at->diffInMinutes(now())) <= 10 ;
    }
    public function sendNotification(Ticket $ticket, User $user){
        $recipient = null;
        if($user->id === $ticket->user_id){
            if($ticket->agent_id){
                $recipient = User::find($ticket->agent_id);
            }
        }
        else{
            $recipient = User::find($ticket->user_id);
        }
        //Caso 3 fallback: Si después de lo anterior nadie va a recibir la notificación buscamos a un administrador
        if(! $recipient){
            $recipient = User::whereHas('rol', fn($q)=>
                $q->where('name', RolEnum::ADMIN)
            )->first();
        }
        if($recipient && $recipient->id !== $user->id){
            //Recipient es el que recibe la notificación y $user es el que realiza la acción.
            $recipient->notify(new TicketUpdatedNotification($ticket,$user));
        }

    }
}
