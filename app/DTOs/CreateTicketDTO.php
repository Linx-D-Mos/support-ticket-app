<?php

namespace App\DTOs;

readonly class CreateTicketDTO
{
    public function __construct(
        public string $title,
        public string $priority,
        public int $userId,
        public ?array $files = null,
        public ?array $labels = null,
    ) {}
    public static function fromRequest($request): self
    {
        return new self(
            title: $request->input('title'),
            priority: $request->input('priority'),
            userId: $request->input('user_id'),
            files: $request->file('files'),
            labels: $request->input('labels'),
        );
    }
}
