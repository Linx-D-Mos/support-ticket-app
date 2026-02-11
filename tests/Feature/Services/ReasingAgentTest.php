<?php

use App\Events\TicketAgentReassigned;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignAgentNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

test('Can launch the event, listener and notification', function () {

    Notification::fake();

    $user = User::factory()->admin()->create();
    $agent = User::factory()->agent()->create();
    $customer = User::factory()->customer()->create();
    $ticket = Ticket::factory()->createdBy($customer)->create();

    $response = $this->actingAs($user)
    ->putJson("/api/tickets/{$ticket->id}/assign", 
    ['agent_id' => $agent->id]);

    $response->assertStatus(200);

    Notification::assertSentTo(
        $customer, TicketAssignAgentNotification::class
    );


});
