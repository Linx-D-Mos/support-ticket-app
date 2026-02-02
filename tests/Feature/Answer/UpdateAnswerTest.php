<?php

use App\Models\Answer;
use App\Models\Ticket;
use App\Models\User;

test('Can update a answer', function () {
    $user = User::factory()->customer()->create();
    $ticket = Ticket::factory()->createdBy($user)->create();
    $answer = Answer::factory()->assignedTo($ticket)->createBy($user)->create(['body' => 'arrenpujala y remangala']);

    $this->actingAs($user)
    ->patchJson("/api/tickets/{$ticket->id}/answers/{$answer->id}",['body' => 'oye traicionera'])
    ->assertStatus(200);

    expect($answer->refresh()->body)
    ->toBe('oye traicionera');


});
