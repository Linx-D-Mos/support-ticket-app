<?php

use App\Models\Ticket;
use App\Models\User;

test('Can delete a ticket', function () {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin)
    ->deleteJson("/api/tickets/{$ticket->id}");

    $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
});
