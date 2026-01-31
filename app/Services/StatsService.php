<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function statsCreate()
    {
        $total_tickets = Ticket::count();
        $open_tickets = Ticket::where('status', Status::OPEN)->count();
        $closed_tickets = Ticket::where('status', Status::CLOSED)->count();
        $priority_distribution = Ticket::select('priority', DB::raw('count(*) as total'))->groupBy('priority')
            ->pluck('total', 'priority'); 
        return [
            'total_tickets' => $total_tickets,
            'open_tickets' => $open_tickets,
            'closed_tickets' => $closed_tickets,
            'priority_distribution' => $priority_distribution,
        ];
    }
}
