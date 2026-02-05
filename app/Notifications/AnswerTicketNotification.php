<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnswerTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public Answer $answer,
        public User $user,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting("Saludos, {$notifiable->name}")
            ->line("Se ha registrado una nueva respuesta en el ticket #{$this->ticket->id} ")
            ->line("Por parte del usuario: {$this->user->name}")
            ->action('Respuesta creada', url("/api/tickets/{$this->ticket->id}/answers/{$this->answer->id}"))
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
            'answer' => $this->answer->body,
            'message' => "El usuario {$this->user->name} respondio al ticket {$this->ticket->id}",
            'action_url' => "/api/tickets/{$this->ticket->id}/answers/{$this->answer->id}",
        ];
    }
}
