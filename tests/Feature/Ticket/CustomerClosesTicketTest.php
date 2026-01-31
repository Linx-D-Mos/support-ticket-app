<?php

use App\Enums\Status;
use App\Models\Ticket;
use App\Models\User;

test('customer can closes a ticket', function () {
    $customer = User::factory()->customer()->create();
    $ticket = Ticket::factory()->create(['user_id' => $customer->id]);

    $this->actingAs($customer)
        ->patchJson("api/tickets/{$ticket->id}/close")
        ->assertStatus(200);

    expect($ticket->refresh()->status)->toBe(Status::CLOSED);
    expect($ticket->refresh()->close_at)
    ->not()
    ->toBeNull();
});

test('customer only can closes his own tickets', function () {
    $customer = User::factory()->customer()->create();
    $hacker = User::factory()->customer()->create();
    $ticket = Ticket::factory()->create(['user_id' => $customer->id]);

    $this->actingAs($hacker)
        ->patchJson("api/tickets/{$ticket->id}/close")
        ->assertForbidden(200);
});

test('Admin can closes tickets', function () {
    $customer = User::factory()->customer()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['user_id' => $customer->id]);

    $this->actingAs($admin)
        ->patchJson("api/tickets/{$ticket->id}/close")
        ->assertStatus(200);
    expect($ticket->refresh()->status)->toBe(Status::CLOSED);
    expect($ticket->refresh()->close_at)
    ->not()
    ->toBeNull();
});
