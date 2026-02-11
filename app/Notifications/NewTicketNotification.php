<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Ticket $ticket)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting("Saludos,{$notifiable->name}")
            ->line("Se ha creado un nuevo ticket #{$this->ticket->id}.")
            ->action('Ticket creado', url("/api/tickets/{$this->ticket->id}"))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    // Base de Datos (Persistencia)
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => '¡Nuevo Ticket Creado!', 
            'message' => "El usuario {$this->ticket->user->name} creó el ticket: {$this->ticket->title}", // <--- CAMBIO: Mensaje claro
            'link' => "/api/tickets/{$this->ticket->id}", 
            'type' => 'ticket_created'
        ];
    }

    // WebSocket (Tiempo Real)
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id, 
            'ticket_id' => $this->ticket->id,
            'title' => '¡Nuevo Ticket Creado!', 
            'message' => "El usuario {$this->ticket->user->name} creó el ticket: {$this->ticket->title}", // <--- COINCIDE con toArray
            'link' => "/api/tickets/{$this->ticket->id}",
        ]);
    }
}
