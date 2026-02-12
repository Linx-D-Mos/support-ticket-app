<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCloseNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $message;
    public string $link;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public User $user
    ) {
        $this->title = "¡Ticket cerrado!";
        $this->message = "¡El ticket #{$this->ticket->id} ha sido cerrado por {$this->user->name}!";
        $this->link = "/api/tickets/{$this->ticket->id}";
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
            ->greeting("Saludos, {$notifiable->name}")
            ->line("El " . $this->role($this->user) . "{$this->user->name} ha cerrado el ticket #{$this->ticket->id}")
            ->action($this->title, url($this->link))
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
            'user_id' => $this->ticket->user->id,
            'agent_id' => $this->ticket->user->id ?? null,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'ticket_id' => $this->ticket->id,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link
        ]);
    }
    public function role(User $user): string
    {
        $texto = "usuario";
        if ($user->isAdmin()) {
            $texto = "administrador";
        }else if($user->isAgent()){
            $texto = "agente";
        }
        return $texto;
    }
}
