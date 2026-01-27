<?php

namespace App\Services;

use App\DTOs\CreateTicketDTO;
use App\Enums\Status;
use App\Events\TicketCreated;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketService
{
    public function createTicket(CreateTicketDTO $data): Ticket
    {
        $ticket = DB::transaction(
            function () use ($data) {
                $ticket = Ticket::create([
                    'user_id' => $data->userId,
                    'title' => $data->title,
                    'priority' => $data->priority,
                    'status' => Status::OPEN,
                ]);
                
                if ($data->files) {
                    foreach ($data->files as $file) {
                        $path = $file->store('tickets/attachments', 'public');
                        $ticket->files()->create(['file_path' => $path]);
                    }
                }
                if($data->labels){
                    $ticket->labels()->attach($data->labels);
                }
                
                return $ticket;
            }
        );
        if (! $ticket) {
            throw new Exception('klk');
        }
        TicketCreated::dispatch($ticket);
        return $ticket;
    }
}
