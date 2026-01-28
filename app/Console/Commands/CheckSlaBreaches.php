<?php

namespace App\Console\Commands;

use App\Enums\Priority;
use App\Enums\Status;
use App\Events\TicketEscalated;
use App\Models\Ticket;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $tickets = Ticket::where('created_at', '<=', now()->subHours(2))
            ->where('status', Status::OPEN)
            ->where('priority', Priority::HIGH)
            ->where('last_reply_at', null)->cursor();
        foreach ($tickets as $ticket) {
            $ticket->update(['status' => Status::ELEVATED]);
            // Pista visual
            TicketEscalated::dispatch($ticket);
        }
    }
}
