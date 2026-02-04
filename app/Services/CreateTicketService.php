<?php

namespace App\Services;

use App\DTOs\CreateTicketDTO;
use App\Enums\Status;
use App\Events\TicketCreated;
use App\Models\Label;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketService
{
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

                // if ($dto->files) {
                //     foreach ($dto->files as $file) {
                //         $path = $file->store('tickets/attachments', 'public');
                //         $ticket->files()->create(['file_path' => $path]);
                //     }
                // }
                $service = app(FileService::class);
                $ticket = $service->storeFile($ticket,$dto->files);

                return $ticket['files'];
            }
        );
        if (! $ticket) {
            throw new Exception('klk');
        }
        TicketCreated::dispatch($ticket);
        return $ticket->load('labels','files');
    }
}
