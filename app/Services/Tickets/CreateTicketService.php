<?php

namespace App\Services\Tickets;

use App\DTOs\CreateTicketDTO;
use App\Enums\Status;
use App\Events\TicketCreated;
use App\Events\TicketCreatedBroadcast;
use App\Models\Label;
use App\Models\Ticket;
use App\Models\User;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketService
{
    public function __construct(
        protected FileService $fileService
    ) {}
    public function createTicket(CreateTicketDTO $dto, User $user): Ticket
    {
        $ticket = DB::transaction(
            function () use ($dto) {
                $ticket = Ticket::create([
                    'user_id' => $dto->userId,
                    'title' => $dto->title,
                    'priority' => $dto->priority,
                    'status' => Status::OPEN,
                ]);
                
                // Sincronizamos las etiquetas si vienen en el DTO
                if ($dto->labels) {
                    $labelIds = Label::whereIn('name', $dto->labels)->pluck('id');
                    $ticket->labels()->sync($labelIds);
                }

                $ticket = $this->fileService->storeFile($ticket, $dto->files);
                return $ticket->load(['files', 'labels','user','agent','answers']);
            }
        );
        if (! $ticket) {
            throw new Exception('No se pudo crear el ticket correctamente');
        }
        TicketCreated::dispatch($ticket, $user);
        broadcast(new TicketCreatedBroadcast($ticket));
        return $ticket;
    }
}
