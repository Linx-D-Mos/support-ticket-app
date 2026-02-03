<?php

use App\Models\Ticket;
use App\Models\User;
use App\Services\AddAgentService;

test('example', function () {
    $ticket = Ticket::factory()->assignedTo(User::factory()->agent()->create())->create();
    $agent = User::factory()->agent()->create();

    $service = new AddAgentService();
    
    expect(fn() =>  $service->addAgent($ticket,$agent->id))
    ->toThrow(Exception::class);



});
