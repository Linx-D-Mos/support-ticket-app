<?php

namespace App\DTOs;

readonly Class UpdateAnswerDTO{
    public function __construct(
        public string $body,
    )
    {}
    public static function fromRequest($request) : self
    {
        return new self(
            body: $request->input('body'),
        );
    }
}