<?php

use App\Enums\Priority;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Travel;

test('An user can edit the title and priority of a ticket', function () {
    $customer = User::factory()->customer()->create();
    $ticket = Ticket::factory()->createdBy($customer)->create();

    $this->actingAs($customer)
        ->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'e esta ñema',
            'priority' => 'medium'
        ])
        ->assertStatus(200);
    expect($ticket->refresh()->title)
        ->toBe('e esta ñema');
    expect($ticket->refresh()->priority)
        ->toBe(Priority::MEDIUM);

    $this->travelTo(now()->addMinutes(15));
    
    $this->actingAs($customer)
        ->patchJson("/api/tickets/{$ticket->id}", [
            'title' => 'e esta ñema',
            'priority' => 'medium'
        ])
        ->assertStatus(403)
        ->assertJson(['message' => 'No puedes editar este ticket, solo está permitido hasta 10 min después de crearlo']);
        
    $this->travelBack();
});
