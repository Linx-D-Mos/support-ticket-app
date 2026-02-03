<?php

namespace App\Services;

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AddAgentService
{
    public function addAgent(Ticket $ticket, int $agent): Ticket
    {
        return DB::transaction(function () use ($ticket, $agent) {
            //Esto se asegura que realmente tengamos el ticket actualizado en cuanto a sus datos e información
            $ticket = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if ($this->ticketValidation($ticket)) {
                throw new Exception('No le puedes asignar un agente a este ticket');
            }
            $agent = $this->userValidation($agent);
            if (! $agent) {
                throw new Exception('Este usuario no existe en la base de datos');
            }
            if (! $this->rolValidation($agent)) {
                throw new Exception('Este usuario no es del rol agente');
            }

            $ticket->update([
                'agent_id' => $agent->id,
                'status' => Status::INPROGRESS,
            ]);

            return $ticket;
        });
    }
    public function ticketValidation(Ticket $ticket)
    {
        return ($ticket->status === Status::CLOSED || $ticket->status === Status::RESOLVED) || ($ticket->agent_id !== null);
    }
    public function userValidation(int $agent_id)
    {
        return User::find($agent_id);
    }
    public function rolValidation(User $agent)
    {
        return $agent->rol()->where('name', RolEnum::AGENT)->exists();
    }
}
