<?php

use App\Enums\EventEnum;
use App\Enums\Status;
use App\Models\Answer;
use App\Models\Audit;
use App\Models\File;
use App\Models\Ticket;
 

test('can audit the creation of a model', function (){
    $answer = Answer::factory()->create();
    $audit = Audit::first();
    expect($audit->event)->toBe(EventEnum::CREATED->value);

});
test('can audit an update of a any model', function () {
    $ticket = Ticket::factory()->create();
    
    // Al actualizar, se crea el segundo audit
    $ticket->update(['status' => Status::INPROGRESS]);

    // MEJOR FORMA: Buscar el último audit generado para este ticket
    $audit = Audit::query()
        ->where('auditable_type', Ticket::class)
        ->where('auditable_id', $ticket->id)
        ->latest() // Obtiene el más reciente
        ->first();

    // Verificamos que exista antes de probar sus propiedades
    expect($audit)->not->toBeNull();

    // Nota: Usa ->value para comparar si en DB guardaste el valor escalar
    expect($audit->event)->toBe(EventEnum::UPDATED->value);
    expect($audit->old_values['status'])->toBe(Status::OPEN->value); // Asumiendo que Status es un Enum
    expect($audit->new_values['status'])->toBe(Status::INPROGRESS->value);
});
