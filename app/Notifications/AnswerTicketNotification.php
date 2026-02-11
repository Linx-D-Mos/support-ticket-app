<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnswerTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Answer $answer,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting("Saludos, {$notifiable->name}")
            ->line("Se ha registrado una nueva respuesta en el ticket #{$this->answer->ticket->id} ")
            ->line("Por parte del usuario: {$this->answer->user->name}")
            ->action('Respuesta creada', url("/tickets/{$this->answer->ticket->id}/answers/{$this->answer->id}"))
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
            'ticket_id' => $this->answer->ticket->id,
            'answer_id' => $this->answer->id,
            'answer' => $this->answer->body,
            'title' => "¡Nueva respuesta en el ticket #{$this->answer->ticket->id}!",
            'message' => "El usuario {$this->answer->user->name} respondio al ticket {$this->answer->ticket->id}",
            'action_url' => "/tickets/{$this->answer->ticket->id}/answers/{$this->answer->id}",
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'ticket_id' => $this->answer->ticket->id,
            'answer_id' => $this->answer->id,
            'title' => "¡Nueva respuesta en el ticket #{$this->answer->ticket->id}!",
            'message' => "El usuario {$this->answer->user->name} respondio al ticket {$this->answer->ticket->id}",
            'link' => "/tickets/{$this->answer->ticket->id}/answers/{$this->answer->id}",
        ]);
    }
}
