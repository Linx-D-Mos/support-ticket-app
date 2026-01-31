<?php

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;

test('cast and agent relationship', function(){
    
    $agentRolId = Rol::where('name', RolEnum::AGENT->value)->firstOrFail()->id;
    $agent = User::factory()->create(['rol_id' => $agentRolId]);
    Ticket::factory()->assignedTo($agent)->create();
    $this->assertDatabaseHas('tickets', ['status' => Status::INPROGRESS]);
});
