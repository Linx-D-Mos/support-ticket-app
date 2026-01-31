<?php

use App\Enums\RolEnum;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

test('can create a ticket', function () {
    Storage::fake('public');
    $customerRolId = Rol::where('name', RolEnum::CUSTOMER->value)->firstOrFail()->id;
    $agentRolId = Rol::where('name', RolEnum::AGENT->value)->firstOrFail()->id;
    $customer = User::factory()->create(['rol_id' => $customerRolId]);
    $agent = User::factory()->create(['rol_id' => $agentRolId]);
    $pdf = UploadedFile::fake()->create('guide.pdf', 500);

    $ticket = Ticket::factory()->assignedTo($agent)->create(['user_id' => $customer->id]);

    $response = $this->actingAs($agent)
        ->postJson(
            "/api/tickets/{$ticket->id}/answers",
            [
                'body' => 'Su problema se debe dado que...',
                'user_id' => $agent->id,
                'files' => [$pdf],
            ]
        );
    $response->assertCreated();
});
test('user cannot answer a ticket that does not belong to them', function () {
    // 1. Arrange
    $ticketOwner = User::factory()->create(['rol_id' => Rol::where('name', RolEnum::CUSTOMER->value)->value('id')]);
    $intruder = User::factory()->create(['rol_id' => Rol::where('name', RolEnum::CUSTOMER->value)->value('id')]);
    
    $ticket = Ticket::factory()->create(['user_id' => $ticketOwner->id]);

    // 2. Act & Assert
    // El intruso intenta responder el ticket del dueño
    $this->actingAs($intruder)
        ->postJson("/api/tickets/{$ticket->id}/answers", [
            'body' => 'Soy un hacker intentando responder',
            'user_id' => $intruder->id
        ])
        ->assertForbidden(); // Esperamos un 403
});