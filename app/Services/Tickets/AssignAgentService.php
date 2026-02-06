<?php

namespace App\Services\Tickets;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Events\TicketAgentReassigned;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AssignAgentService
{
    public function assignAgent(Ticket $ticket, int $agent_id): Ticket
    {
        return DB::transaction(function () use ($ticket, $agent_id) {
            // En lugar de solo $ticket->lockForUpdate();
            //ponemos la busqueda del ticket para asegurarnos que todos sus datos esten completos
            $ticket = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if ($this->ticketValidation($ticket)) {
                throw new Exception('No se puede reasignar un ticket que está cerrado.');
            }
            $agent = $this->userValidation($agent_id);

            if (! $this->rolValidation($agent)) {
                throw new Exception('El usuario seleccionado no tiene el rol de agente.');
            }

            $ticket->update([
                'agent_id' => $agent->id,
                'status' => Status::INPROGRESS,
            ]);
            TicketAgentReassigned::dispatch($ticket);
            return $ticket;
        });
    }
    public function ticketValidation(Ticket $ticket)
    {
        return ($ticket->status === Status::CLOSED);
    }
    public function userValidation(int $agent_id)
    {
        return User::findOrFail($agent_id);
    }
    public function rolValidation(User $agent)
    {
        return $agent->rol()->where('name', RolEnum::AGENT)->exists();
    }
}
