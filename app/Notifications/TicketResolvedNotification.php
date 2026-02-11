<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketResolvedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public User $user
    ) {
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
            ->greeting("Saludos, $notifiable->name")
            ->line("El {$this->user->name} ({$this->user->rol->name->value}) ha marcado el ticket #{$this->ticket->id} como resuelto.")
            ->action("Ticket #{$this->ticket->id} ha sido resuelto.", url("/tickets/{$this->ticket->id}"))
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
            'agent_id' => $this->ticket->agent->id,
            'title' => "¡Ticket resuelto!",
            'message' => "Ticket #{$this->ticket->id} ha sido resuelto por {$this->user->name} ({$this->user->rol->name->value}).",
            'link' => "/tickets/{$this->ticket->id}",
        ];
    }
    public function toBroadcast(): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id' => $this->ticket->id,
            'agent_id' => $this->ticket->agent->id,
            'title' => "¡Ticket resuelto!",
            'message' => "Ticket #{$this->ticket->id} ha sido resuelto {$this->user->name} ({$this->user->rol->name->value}).",
            'link' => "/tickets/{$this->ticket->id}",
        ]);
    }
}
