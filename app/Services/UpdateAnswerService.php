<?php

namespace App\Services;

use App\DTOs\UpdateAnswerDTO;
use App\Models\Answer;
use Exception;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class UpdateAnswerService
{
    public function updateAnswer(Answer $answer, UpdateAnswerDTO $dto): Answer
    {
        return DB::transaction(function () use ($dto, $answer) {
            $answer->update(['body' =>  $dto->body]);
            $service = App(FileService::class);
            $answer = $service->storeFile($answer, $dto->files);
            return $answer->load('user', 'files');
        });
    }
}
