<?php

namespace App\DTOs;

readonly Class UpdateAnswerDTO{
    public function __construct(
        public string $body,
        public ?array $files = [],
    )
    {}
    public static function fromRequest($request) : self
    {
        return new self(
            body: $request->input('body'),
            files: $request->input('files'),
        );
    }
}