<?php

use App\Enums\Status;
use App\Models\Ticket;
use App\Models\User;

test('An agente con resolve a ticket', function () {
    $user = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($user)->create();

    $this->actingAs($user)
    ->patchJson("api/tickets/{$ticket->id}/resolve")
    ->assertStatus(200);

    expect($ticket->refresh()->status)
    ->toBe(Status::RESOLVED);
    expect($ticket->refresh()->resolve_at)
    ->not()
    ->toBeNull();

});

test('An admin con resolve a ticket', function () {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->assignedTo($user)->create();

    $this->actingAs($user)
    ->patchJson("api/tickets/{$ticket->id}/resolve")
    ->assertStatus(200);

    expect($ticket->refresh()->status)
    ->toBe(Status::RESOLVED);
    expect($ticket->refresh()->resolve_at)
    ->not()
    ->toBeNull();

});
test('Agente can only close their own tickets', function () {
    $user = User::factory()->admin()->create();
    $agent_externo = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($user)->create();

    $this->actingAs($agent_externo)
    ->patchJson("api/tickets/{$ticket->id}/resolve")
    ->assertForbidden();

});
