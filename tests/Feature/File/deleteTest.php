<?php

use App\DTOs\CreateTicketDTO;
use App\Enums\Priority;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('Can delete a file', function () {
    Storage::fake('public');
    $user = User::factory()->customer()->create();
    $file = UploadedFile::fake()->create('archivo.pdf')->size(100);
    $ticket = Ticket::factory()->createdBy($user)->create();
    $path = $file->store('si/files', 'public');
    $ticket->files()->create(['file_path' => $path]);

    $this->actingAs($user)
        ->deleteJson("/api/files/{$ticket->files()->first()->id}")
        ->assertNoContent();
});
test('Cant delete a file without authorization', function () {
    Storage::fake('public');
    $user = User::factory()->customer()->create(); 
    $intruder = User::factory()->customer()->create();
    $file = UploadedFile::fake()->create('archivo.pdf')->size(100);
    $ticket = Ticket::factory()->createdBy($user)->create();
    $path = $file->store('si/files', 'public');
    $ticket->files()->create(['file_path' => $path]);

    $this->actingAs($intruder)
        ->deleteJson("/api/files/{$ticket->files()->first()->id}")
        ->assertForbidden();
});
