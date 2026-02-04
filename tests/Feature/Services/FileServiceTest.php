<?php

use App\Models\File;
use App\Models\Ticket;
use App\Services\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


test('Can storage a file', function () {
    Storage::fake('public');
    $ticket = Ticket::factory()->create();
    $service = app(FileService::class);
    $image = UploadedFile::fake()->create('imagen.png')->size(100);
    $ticket = $service->storeFile($ticket, $image);
    
    $file = $ticket->files->first();
    Storage::disk('public')->assertExists($file->file_path);
    $this->assertDatabaseHas('files', ['id' => $file->id]);
    expect($ticket->files)->toHaveCount(1);

});

test('Can download a file', function (){
    Storage::fake('public');
    $file = File::factory()->create();
    $service = app(FileService::class);

    $url = $service->downloadFile($file);
    
});