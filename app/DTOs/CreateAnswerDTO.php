<?php

namespace App\DTOs;

readonly Class CreateAnswerDTO{
    public function __construct(
        public int $ticket_id,
        public int $user_id,
        public string $body,
        public ?array $files = null,

    ) {}
    public static function fromRequest($request): self
    {
        return new self(
            ticket_id: $request->input('ticked_id'),
            user_id: $request->input('user_id'),
            body: $request->input('body'),
            files: $request->file('files')
        );
    }
}