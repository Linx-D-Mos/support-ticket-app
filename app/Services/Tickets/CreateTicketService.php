<?php

namespace App\Services\Tickets;

use App\DTOs\CreateTicketDTO;
use App\Enums\Status;
use App\Events\TicketCreated;
use App\Models\Label;
use App\Models\Ticket;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketService
{
    public function __construct(
        protected FileService $fileService
    ) {}
    public function createTicket(CreateTicketDTO $dto): Ticket
    {
        $ticket = DB::transaction(
            function () use ($dto) {
                $ticket = Ticket::create([
                    'user_id' => $dto->userId,
                    'title' => $dto->title,
                    'priority' => $dto->priority,
                    'status' => Status::OPEN,
                ]);

                $ticket = $this->fileService->storeFile($ticket, $dto->files);
                return $ticket->load('files');
            }
        );
        if (! $ticket) {
            throw new Exception('klk');
        }
        TicketCreated::dispatch($ticket);
        return $ticket->load('labels', 'files');
    }
}
