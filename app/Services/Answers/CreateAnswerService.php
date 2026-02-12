<?php

namespace App\Services\Answers;

use App\DTOs\CreateAnswerDTO;
use App\Events\Answers\AnswerCreated;
use App\Events\TicketMessageSent;
use App\Models\Answer;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateAnswerService
{
    public function __construct(
        protected FileService $fileService
    ) {}
    public function CreateAnswer(CreateAnswerDTO $dto): Answer
    {
        $answer = DB::transaction(
            function () use ($dto) {
                $answer = Answer::create(
                    [
                        'ticket_id' => $dto->ticket_id,
                        'user_id' => $dto->user_id,
                        'body' => $dto->body,
                    ]
                );
                if (! $answer) {
                    throw new Exception('No se puedo crear la respuesta correctamente');
                }

                return $this->fileService->storeFile($answer, $dto->files);
            }
        );
        $answer->load(['ticket.user', 'ticket.agent', 'user.rol', 'files']);
        AnswerCreated::dispatch($answer);
        broadcast(new TicketMessageSent($answer));
        return $answer;
    }
}
