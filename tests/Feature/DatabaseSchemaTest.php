<?php

use App\Enums\RolEnum;
use App\Enums\Type;
use App\Models\Label;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Events\DatabaseRefreshed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('database relationship and models', function () {

    foreach (Type::cases() as $type) {
        Label::firstOrCreate([
            'name' => $type->value
        ], [
            'description' => fake()->paragraph()
        ]);
        Rol::firstOrCreate([
            'name' => $type->value
        ]);
    }
    $agent = User::factory()->create(['rol_id' => 2]);
    $customer = User::factory()->create(['rol_id' => 3]);

    $ticket = Ticket::factory()->create([
        'user_id' => $customer,
        'status' => 'open'
    ]);

    $ticket->update(['agent_id' => $agent->id]);
    $ticket->files()->create(['file_path' => 'evidence.jpg']);

    // 3. ASSERT (Verificar resultados)

    // Verificación 1: El ticket debe tener el agente asignado
    expect($ticket->fresh()->agent_id)
        ->toBe($agent->id);

    // Verificación 2: El ticket debe tener 1 archivo asociado
    expect($ticket->files)
        ->toHaveCount(1)
        ->first()->file_path->toBe('evidence.jpg');

    // Verificación 3: Integridad de Base de Datos (Postgres)
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'agent_id' => $agent->id,
        'status' => 'open' // Verificando default o asignación
    ]);
});
