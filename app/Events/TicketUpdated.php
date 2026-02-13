<?php

namespace App\Events;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Ticket $ticket
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        //Para la vista de detalle (chat/badges): Canal específico del ticket.
        $channels[] = new PrivateChannel("ticket.{$this->ticket->id}");
        
        //2. PAra la lista de Tickets (tabla):
        //A. Si es para el staff, enviamos al canal global de staff
        $channels[] = new PrivateChannel("tickets"); //Solo admins y agentes (ver cambios).

        //B. Vital: Para el cliente dueño, enviamos a su canal personal
        // Así el cliente A solo recibe updates de sus propios tickets en su lista.
        $channels[] = new PrivateChannel("App.Models.User{$this->ticket->user_id}");
        
        return $channels;
    }
    public function broadcastWith(): array
    {
        return [
            'ticket' => (new TicketResource($this->ticket))->resolve()
        ];
    }
}
