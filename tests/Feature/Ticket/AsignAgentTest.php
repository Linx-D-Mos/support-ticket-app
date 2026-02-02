<?php

use App\Models\Ticket;
use App\Models\User;

test('Can asign to another agent a ticket as agent', function () {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();
    $sustitute_agent = User::factory()->agent()->create();
    $this->actingAs($agent)
        ->putJson("/api/tickets/{$ticket->id}/assign", ['agent_id' => $sustitute_agent->id])
        ->assertStatus(200);
    expect($ticket->refresh()->agent_id)
        ->toBe($sustitute_agent->id);
});
test('cant assing a ticket to a non Agent user', function () {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();
    $sustitute_agent = User::factory()->customer()->create();
    $this->actingAs($agent)
    ->putJson("/api/tickets/{$ticket->id}/assign", ['agent_id' => $sustitute_agent->id])
    ->assertStatus(500)
    ->assertJson(['message' => 'El usuario seleccionado no tiene el rol de agente.']);
    
    expect($ticket->refresh()->agent_id)
    ->not()
    ->toBe($sustitute_agent->id);
});
test('forbidden access', function () {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();
    $sustitute_agent = User::factory()->customer()->create();
    $this->actingAs($sustitute_agent)
    ->putJson("/api/tickets/{$ticket->id}/assign", ['agent_id' => $agent->id])
    ->assertForbidden();
    
    expect($ticket->refresh()->agent_id)
    ->not()
    ->toBe($sustitute_agent->id);
});
