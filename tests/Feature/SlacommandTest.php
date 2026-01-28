<?php

use App\Enums\Status;
use App\Events\TicketEscalated;
use App\Models\Ticket;

test('SLA ticket', function () {
    Event::fake();
    $this->travelTo(now()->subHours(3));
    $ticketA = Ticket::factory()->high()->create();
    $this->travelBack();
    $ticketB = Ticket::factory()->high()->create();
    $this->artisan('tickets:check-sla')->assertExitCode(0);
    
    expect($ticketA->refresh()->status)->toBe(Status::ELEVATED);
    Event::assertDispatched(TicketEscalated::class, function ($event) use ($ticketA) {
        return $event->ticket->id === $ticketA->id;
    });
});
