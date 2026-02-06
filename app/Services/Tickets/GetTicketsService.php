<?php

namespace App\Services\Tickets;

use App\Enums\RolEnum;
use App\Models\Ticket;
use App\Models\User;
use Exception;

class GetTicketsService
{
    public function getTickets(User $user,array $filters = [] )
    {

        $query = Ticket::query();
        if ($user->hasRole(RolEnum::ADMIN)) {
            $query = Ticket::getTicketsAdmin();
        } elseif ($user->hasRole(RolEnum::AGENT)) {
            $query = Ticket::getTicketsAgent($user);
        } elseif ($user->hasRole(RolEnum::CUSTOMER)) {
            $query = Ticket::getTicketsCustomer($user);
        } else {
            throw new Exception('No tienes permiso para realizar esto');
        }
        return $query
        ->when($filters['status'] ?? null, fn ($q, $status) => $q->status($status))
        ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->priority($priority))
        ->when($filters['search'] ?? null, fn ($q, $search) => $q->search($search))
        ->latest()
        ->paginate(10);
    }
}
