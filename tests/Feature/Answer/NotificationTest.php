<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\AnswerTicketNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('Notify an admin user about the creation of an answer', function () {
    Notification::fake();
    
    $customer = User::factory()->customer()->create();
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->createdBy($customer)->assignedTo($agent)->create();
    
    $response = $this->actingAs($agent)
    ->postJson("api/tickets/{$ticket->id}/answers",[
        'body' => 'Melissa mi amor',
    ]);
    $response->assertCreated();

    //Verificamos que se envió la notificación al cliente.
    Notification::assertSentTo(
        $customer, 
        AnswerTicketNotification::class,
        //Para cada notificación que coincida, aplicamos una función
        //$notification es la instancia real de la notificacion que se creo.
        //$channel array de los canales que devolvió el método y la via.
        function ($notification, $channels) use ($ticket){
        
        //Aqui basicamente nos aseguramos que nos devuelva los canales correctos por donde se envio la notificación
        //y que el ID del ticket presente en la notificación este correcto.
        return in_array('mail', $channels) &&
        in_array('database', $channels) &&
        $notification->ticket->id === $ticket->id;
    }
    );

});
