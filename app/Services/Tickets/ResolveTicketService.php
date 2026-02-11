<?php

namespace App\Services\Tickets;

use App\Enums\Status;
use App\Events\TicketResolved;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\ResolveTicketNotification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResolveTicketService
{
    public function __construct(
        public NotificationService $service,
    ) {}
    public function resolveTicket(Ticket $ticket, User $user): Ticket
    {
        $ticket->update([
            'status' => Status::RESOLVED,
            'resolved_at' => Carbon::now()
        ]);

        TicketResolved::dispatch($ticket->load('user','agent'), $user);
        // $this->service->sendNotification($ticket, $user, new ResolveTicketNotification($ticket, $user));
        return $ticket->load('labels', 'files', 'user', 'agent');
    }
}
