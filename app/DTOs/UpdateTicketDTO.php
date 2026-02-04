<?php

namespace App\DTOs;

use App\Enums\Priority;

readonly Class  UpdateTicketDTO
{
    public function __construct(
        public int $ticket_id,
        public int $user_id,
        public string $title,
        public string $priority,
        public ?array  $labels = null,
        public ?array $files = null,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            ticket_id: $request->input('ticket_id'),
            user_id: $request->input('user_id'),
            title: $request->input('title'),
            priority: $request->input('priority'),
            labels: $request->input('labels'),
            files: $request->input('files'),
        );
    }
}
