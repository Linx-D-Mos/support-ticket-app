<?php

namespace App\Events;

use App\Models\Answer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Answer $answer
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
        return [
            new PrivateChannel("ticket.{$this->answer->ticket_id}"),
        ];
    }
    /**
     * Define QUÉ DATOS EXACTOS se envían por el WebSocket.
     * IMPORTANTE: Por seguridad y rendimiento, NUNCA envíes el modelo completo.
     * Solo envía lo que el frontend necesita para pintar la burbuja del chat.
     */
    public function broadcastWith(): array
    {
        $this->answer->loadMissing('user.rol','files');
        return [
            'id' => $this->answer->id,
            'body' => $this->answer->body,
            'sender_id' => $this->answer->user_id,
            'sender_name' => $this->answer->user->name,
            'sender_rol' => $this->answer->user->rol->name->value,
            'created_at' => $this->answer->created_at->toISOString(),
            'files' => $this->answer->files ?? [],
        ];
    }
}
