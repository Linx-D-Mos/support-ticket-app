<?php

namespace App\Services\Answers;

use App\DTOs\CreateAnswerDTO;
use App\Enums\RolEnum;
use App\Models\Answer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\AnswerTicketNotification;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateAnswerService
{
    public function __construct(
        protected FileService $fileService
    ) {}
    public function CreateAnswer(CreateAnswerDTO $dto, Ticket $ticket, User $user): Answer
    {
        $answer = DB::transaction(
            function () use ($dto, $ticket, $user) {
                $answer = Answer::create(
                    [
                        'ticket_id' => $dto->ticket_id,
                        'user_id' => $dto->user_id,
                        'body' => $dto->body,
                    ]
                );
                if (! $answer) {
                    throw new Exception('No se puedo crear la respuesta correctamente');
                    // AnswerCreated::dispatch($answer);
                }

                $answer = $this->fileService->storeFile($answer, $dto->files);

                return $answer->load('files', 'ticket', 'user');
            }
        );
        $this->sendNotification($ticket, $answer, $user);
        return $answer->load('files', 'ticket', 'user');
    }
    public function sendNotification(Ticket $ticket, Answer $answer, User $user)
    {
        if ($ticket->user_id == $user->id) {
            if ($ticket->agent_id) {
                $recipient = User::find($ticket->agent_id);
            }
        } else {
            $recipient = User::find($ticket->user_id);
        }
        if (!$recipient) {
            $recipient = User::whereHas(
                'rol',
                fn($q) =>
                $q->where('name', RolEnum::ADMIN)
            )->inRandomOrder()
                ->first();
        }
        if ($recipient && $recipient->id !== $user->id) {
            $recipient->notify(new AnswerTicketNotification($ticket, $answer, $user));
        }
    }
}
