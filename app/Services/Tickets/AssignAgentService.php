<?php

namespace App\Services\Tickets;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Events\TicketAgentReassigned;
use App\Events\TicketUpdated;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AssignAgentService
{
    public function assignAgent(Ticket $ticket, int $agent_id): Ticket
    {
        $ticket = DB::transaction(function () use ($ticket, $agent_id) {
            // En lugar de solo $ticket->lockForUpdate();
            //ponemos la busqueda del ticket para asegurarnos que todos sus datos esten completos
            $ticket = Ticket::with('user', 'agent', 'labels', 'answers')->where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if ($this->ticketValidation($ticket)) {
                throw new Exception('No se puede reasignar un ticket que está cerrado.');
            }
            $agent = $this->userValidation($agent_id);

            if (! $this->rolValidation($agent)) {
                throw new Exception('El usuario seleccionado no tiene el rol de agente.');
            }
            if ($this->agentValidation($agent, $ticket)) {
                throw new Exception('El agente ya se encuentra asignado a este ticket.');
            }

            $ticket->update([
                'agent_id' => $agent->id,
                'status' => Status::INPROGRESS,
            ]);

            return $ticket->load('user', 'agent', 'labels', 'answers');
        });
        if (! $ticket) {
            throw new Exception('Falló en la asignación del agente');
        }
        //Cuando usamos un evento con listener que use ShouldQueue es obligatorio cargar las relaciones necesarias
        //implicadas en el proceso.
        TicketAgentReassigned::dispatch($ticket);
        TicketUpdated::dispatch($ticket);
        return $ticket;
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
    public function agentValidation(User $agent, Ticket $ticket)
    {

        if (!$ticket->agent_id) {
            return false ;
        }
        return $ticket->agent_id === $agent->id;
    }
}
