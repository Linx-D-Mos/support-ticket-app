<?php

namespace App\Services\Answers;

use App\DTOs\UpdateAnswerDTO;
use App\Models\Answer;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class UpdateAnswerService
{
    public function __construct(
        protected FileService $fileService
    )
    {
        throw new \Exception('Not implemented');
    }
    public function updateAnswer(Answer $answer, UpdateAnswerDTO $dto): Answer
    {
        return DB::transaction(function () use ($dto, $answer) {
            $answer->update(['body' =>  $dto->body]);
            $answer = $this->fileService->storeFile($answer, $dto->files);
            return $answer->load('user', 'files');
        });
    }
}
