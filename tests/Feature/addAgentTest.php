<?php

use App\Enums\Status;
use App\Models\Ticket;
use App\Models\User;

test('example', function () {
    $user = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($ticket->status)->toBe(Status::OPEN);
    $this->actingAs($user)->postJson("api/tickets/{$ticket->id}/addAgent", ['agent_id' => $user->id])
        ->assertStatus(200);
    expect($ticket->refresh()
        ->status)
        ->toBe(Status::INPROGRESS);
    expect($ticket->refresh()
        ->agent_id)
        ->toBe($user->id);
});
