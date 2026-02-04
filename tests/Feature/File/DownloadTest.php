<?php

use App\Models\File;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('Can download a file if has the right permissions', function () {
    $user = User::factory()->create();
    $intruder = User::factory()->create();

    $ticket = Ticket::factory()->createdBy($user)->create();
    $ticket->files()->create(['file_path' => 'attachments/fake_file.pdf']);
    $file = $ticket->files->first();
    $response = $this->actingAs($user)
        ->getJson("api/{$file->id}/download");
    $response->assertOk()
        ->assertJsonStructure(['url']);
    $donwloadUrl = $response->json('url');
    $this->assertStringContainsString('/signed/download', $donwloadUrl);
});
