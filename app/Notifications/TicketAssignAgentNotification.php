<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignAgentNotification extends Notification
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
            ->greeting("Saludos, {$notifiable->name}")
            ->line($this->getMessage($notifiable))
            ->action('Ticket', url("/tickets/{$this->ticket->id}"))
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
            'title' => $this->getTitle($notifiable),
            'agent_id' => $this->ticket->agent_id,
            'message' => $this->getMessage($notifiable),
            'link' => "/tickets/{$this->ticket->id}",
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'ticket_id' => $this->ticket->id,
            'title' => $this->getTitle($notifiable),
            'message' => $this->getMessage($notifiable),
            'link' => "/tickets/{$this->ticket->id}",  
        ]);
    }

    private function isAgent(object $notifiable): bool
    {
        return $notifiable->id === $this->ticket->agent_id;
    }

    private function getMessage(object $notifiable): string
    {
        if ($this->isAgent($notifiable)) {
            return "Has sido seleccionado como agente en el ticket #{$this->ticket->id}.";
        }

        return "Ticket #{$this->ticket->id}: Agente asignado {$this->ticket->agent->name}.";
    }

    private function getTitle(object $notifiable): string
    {
        if ($this->isAgent($notifiable)) {
            return "¡Asignación como agente!";
        }

        return "¡Agente asignado a tú ticket!";
    }
}
