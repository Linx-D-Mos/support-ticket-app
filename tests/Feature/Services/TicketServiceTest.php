<?php

use App\DTOs\CreateTicketDTO;
use App\Enums\Priority;
use App\Enums\RolEnum;
use App\Events\TicketCreated;
use App\Listeners\SendTicketCreatedEmail;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CreateTicketService;
use Database\Seeders\RolSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('Service creates ticket and dispatches event', function () {
    Storage::fake('public');
    Event::fake();
    $this->seed(RolSeeder::class);
    $customerRolId = Rol::where('name', RolEnum::CUSTOMER->value)->firstOrFail()->id;
    $user = User::factory()->create(['rol_id' => $customerRolId]);
    $file = UploadedFile::fake()->create('archivo.pdf')->size(100);
    $dto = new CreateTicketDTO(
        title: fake()->sentence(),
        priority: Priority::HIGH->value,
        userId: $user->id,
        files: [$file],
    );
    $service = app(CreateTicketService::class);
    $ticket = $service->createTicket($dto);
    $storedFileRecord = $ticket->files()->first();
    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
    $this->assertDatabaseHas('files', ['fileable_id' => $ticket->id]);
    expect($storedFileRecord)->not->toBeNull();
    Storage::disk('public')->assertExists($storedFileRecord->file_path);
    Event::assertDispatched(TicketCreated::class, function ($event) use ($ticket) {
        return $event->ticket->id === $ticket->id;
    });
});
test('event is wired correctly to the listener and listener is queueable', function () {
    // 1. ARRANGE
    Event::fake();

    // 2. ASSERTION 1: ¿Están conectados?
    // Esto verifica que Laravel sabe que cuando pase A, debe llamar a B.
    Event::assertListening(
        TicketCreated::class,
        SendTicketCreatedEmail::class
    );
});

test('listener implements ShouldQueue', function () {
    // 2. ASSERTION 2: ¿Es asíncrono?
    // Usamos reflexión para preguntar a la clase: "¿Oye, tú implementas la interfaz de cola?"
    $reflection = new ReflectionClass(SendTicketCreatedEmail::class);

    expect($reflection->implementsInterface(ShouldQueue::class))
        ->toBeTrue('El Listener debe implementar ShouldQueue para ser asíncrono');
});
