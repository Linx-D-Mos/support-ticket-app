<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAddedAgentNotification extends Notification
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
        return ['mail','database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->greeting("Saludos, {$notifiable->name}")
            ->line("Se ha asignado un agente a tú ticket #{$this->ticket->id}.")
            ->action('Agente asignado', url("/tickets/{$this->ticket->id}"))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->ticket->user_id,
            'title' => '¡Agente asignado a tú ticket!',
            'message' => "Ticket #{$this->ticket->id}: Agente asignado {$this->ticket->agent->name}",
            'link' => "/api/tickets/{$this->ticket->id}",
            'type' => 'agent_assigned'
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'ticket_id' => $this->ticket->id,
            'title' => '¡Agente asignado a tú ticket!',
            'message' => "Ticket #{$this->ticket->id}: Agente asignado {$this->ticket->agent->name}",
            'link' => "/tickets/{$this->ticket->id}",
        ]);
    }
}
