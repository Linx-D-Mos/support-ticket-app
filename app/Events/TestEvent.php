<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel; // Importante: Canal Público
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// ... otros imports

class TestEvent implements ShouldBroadcast
{
    public function __construct(public string $message, public int $userId) {}

    // Definimos que el canal se llama 'public-updates'
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    // (Opcional) Datos que enviamos al frontend
    public function broadcastWith(): array
    {
        return [
            'content' => $this->message,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}