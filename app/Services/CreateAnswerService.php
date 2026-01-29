<?php

namespace App\Services;

use App\DTOs\CreateAnswerDTO;
use App\Events\AnswerCreated;
use App\Models\Answer;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateAnswerService
{
    public function CreateAnswer(CreateAnswerDTO $dto): Answer
    {
        $answer = DB::transaction(
            function () use ($dto) {
                $answer = Answer::create(
                    [
                        'ticket_id' => $dto->ticket_id,
                        'user_id' => $dto->user_id,
                        'body' => $dto->body,
                        'files' => $dto->files,
                    ]
                );
                if($dto->files){
                    foreach($dto->files as $file){
                        $path = $file->store('answer/attachments', 'public');
                        $answer->files()->create(['file_path' => $path]);
                    }
                }
                return $answer;
            }
        );
         if (! $answer) {
            throw new Exception('No se pudo crear la respuesta adecuadamanete');
        }
        AnswerCreated::dispatch($answer);
        return $answer;
    }
}
