<?php

use App\Enums\RolEnum;
use App\Enums\Status;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;

it('forbidden access to ticket if dont have the autorization', function () {
    $CustomerRoleId = Rol::where('name', RolEnum::CUSTOMER->value)->value('id');
    $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
    $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');
    $userA = User::factory()->create(['rol_id' => $CustomerRoleId]);
    $userB = User::factory()->create(['rol_id' => $CustomerRoleId]);
    $userC = User::factory()->create(['rol_id' => $adminRoleId]);
    $userD = User::factory()->create(['rol_id' => $agentRoleId]);

    $ticket = Ticket::factory()->create(['user_id' => $userA]);

    $response = $this->actingAs($userB)->getJson("api/tickets/{$ticket->id}");
    $response2 = $this->actingAs($userA)->getJson("api/tickets/{$ticket->id}");
    $response3 = $this->actingAs($userC)->getJson("api/tickets/{$ticket->id}");
    $response4 = $this->actingAs($userD)->getJson("api/tickets/{$ticket->id}");
    $response->assertForbidden();
    $response2->assertStatus(200);
    $response3->assertStatus(200);
    $response4->assertStatus(200);
});

it('forbidden access to update a ticket if dont have the autorization', function(){
    $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');
    $userA = User::factory()->create(['rol_id' => $agentRoleId]);
    $userB = User::factory()->create(['rol_id' => $agentRoleId]);

    $ticket = Ticket::factory()->assignedTo($userA)->create();

    $response = $this->actingAs($userB)->putJson("api/tickets/{$ticket->id}" , ['status' => Status::RESOLVED]);
    $response2 = $this->actingAs($userA)->putJson("api/tickets/{$ticket->id}", ['status' => Status::RESOLVED]);
    $response->assertForbidden();
    $response2->assertStatus(200);
});
it('forbidden access to delete a ticket if dont have the autorization', function(){
    $agentRoleId = Rol::where('name', RolEnum::AGENT->value)->value('id');
    $adminRoleId = Rol::where('name', RolEnum::ADMIN->value)->value('id');
    $userA = User::factory()->create(['rol_id' => $agentRoleId]);
    $userB = User::factory()->create(['rol_id' => $adminRoleId]);

    $ticket = Ticket::factory()->assignedTo($userA)->create();

    $response = $this->actingAs($userA)->deleteJson("api/tickets/{$ticket->id}");
    $response2 = $this->actingAs($userB)->deleteJson("api/tickets/{$ticket->id}");
    $response->assertForbidden();
    $response2->assertStatus(204);
});
