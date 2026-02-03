<?php

use App\Enums\EventEnum;
use App\Enums\Status;
use App\Models\Answer;
use App\Models\Audit;
use App\Models\File;
use App\Models\Ticket;

test('can create an audit log', function () {
    $ticket = Ticket::factory()->create();
    $answer = Answer::factory()->assignedTo($ticket);
    $file = File::factory()->create();
    $ticket->update(['status' => Status::INPROGRESS]);
    $audit = Audit::first();

    expect($audit->event)->toBe(EventEnum::UPDATED->value);
    expect($audit->old_values['status'])->toBe(Status::OPEN->value);
    expect($audit->new_values['status'])->toBe(Status::INPROGRESS->value);
});
