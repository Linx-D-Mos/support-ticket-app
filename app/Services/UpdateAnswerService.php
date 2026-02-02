<?php

namespace App\Services;

use App\DTOs\UpdateAnswerDTO;
use App\Models\Answer;
use Exception;

use function Symfony\Component\Clock\now;

class UpdateAnswerService
{
    public function updateAnswer(Answer $answer,UpdateAnswerDTO $dto ) : Answer
    {
        if (!$this->timeValidation($answer)) {
            throw new Exception('No puedes editar esta respuesta, solo está permitido hasta 10 minutos después de crearlo');
        }
        $answer->update(['body' =>  $dto->body]);

        return $answer->load('user', 'files');
    }
    public function timeValidation(Answer $answer)
    {
        return $answer->created_at->diffInMinutes(now()) <= 10;
    }
}
