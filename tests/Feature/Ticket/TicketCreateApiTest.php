<?php

use App\Enums\Priority;
use App\Enums\RolEnum;
use App\Models\Label;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;




test('can create a ticket', function () {
    Storage::fake('public');

    $pdf = UploadedFile::fake()->create('guide.pdf', 5000);
    $customerRolId = Rol::where('name', RolEnum::CUSTOMER->value)->firstOrFail()->id;
    $customer = User::factory()->create(['rol_id' => $customerRolId]);

    $bugLabelId = Label::where('name', 'bug')->first()->id;
    $incidentLabelId = Label::where('name', 'incident')->first()->id;
    $response = $this->actingAs($customer)
        ->postJson('/api/tickets', [
            'title' => 'Error',
            'priority' => Priority::HIGH->value,
            'files' => [$pdf],
            'labels' => [$bugLabelId, $incidentLabelId],
        ]);
    $response->assertCreated();
    $this->assertDatabaseHas('tickets', [
        'title' => 'Error',
        'priority' => 'high',
        'user_id' => $customer->id
    ]);
    $ticket = \App\Models\Ticket::where('title', 'Error')->first();
    $file = $ticket->files()->first();
    Storage::disk('public')->assertExists($file->file_path);
    $this->assertDatabaseHas('label_ticket', [
        'ticket_id' => $ticket->id,
        'label_id' => $incidentLabelId, // Verificamos que el label 1 se asoció
    ]);

    $this->assertDatabaseHas('label_ticket', [
        'ticket_id' => $ticket->id,
        'label_id' => $bugLabelId, // Verificamos que el label 2 se asoció
    ]);
});
