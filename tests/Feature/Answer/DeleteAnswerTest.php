<?php

use App\Models\Answer;
use App\Models\Ticket;
use App\Models\User;

test('a user can delete his own answer', function () {
    $user = User::factory()->customer()->create();
    $ticket = Ticket::factory()->createdBy($user)->create();
    $answer = Answer::factory()->assignedTo($ticket)->createBy($user)->create();

    $this->actingAs($user)
    ->deleteJson("/api/tickets/{$ticket->id}/answers/{$answer->id}")
    ->assertNoContent();

    $this->assertDatabaseMissing('answers',['id' => $answer->id] );

});
test('a user cant delete a non own answer', function () {
    $user = User::factory()->customer()->create();
    $intruder = User::factory()->customer()->create();
    $ticket = Ticket::factory()->createdBy($user)->create();
    $answer = Answer::factory()->assignedTo($ticket)->createBy($user)->create();

    $this->actingAs($intruder)
    ->deleteJson("/api/tickets/{$ticket->id}/answers/{$answer->id}")
    ->assertForbidden();

    $this->assertDatabaseHas('answers',['id' => $answer->id] );

});
