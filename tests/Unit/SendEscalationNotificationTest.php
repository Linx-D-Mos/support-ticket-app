<?php

use App\Events\TicketEscalated;
use App\Listeners\SendEscalationNotification;
use App\Mail\TicketEscalatedMail;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Symfony\Contracts\EventDispatcher\Event;

test('example', function () {
    Mail::fake(); // Interceptamos el cartero real

    $ticket = Ticket::factory()->make(['id' => 99]); 
    $event = new TicketEscalated($ticket);
    $listener = new SendEscalationNotification();
    $listener->handle($event);

    Mail::assertSent(TicketEscalatedMail::class, function ($mail) use ($ticket) {
        return $mail->ticket->id === $ticket->id
        && $mail->hasTo('admin@soporte.com');
    });
});
