<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CloseTicketNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public User $user,
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting("Hi, {$notifiable->name}.")
            ->line("El ticket #{$this->ticket->id}, ha sido cerrado por el {$this->user->rol->name->value} : {$this->user->name}")
            ->action('Revisa la actualización', url("/api/tickets/{$this->ticket->id}"))
            ->line('Gracias por preferirnos!');
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
            'title' => $this->ticket->title,
            'status' => $this->ticket->status->value,
            'message' => "El usuario {$this->user->name} cerro el ticket {$this->ticket->id}",
            'action_url' => url("/api/tickets/{$this->ticket->id}"),
        ];
    }
}
