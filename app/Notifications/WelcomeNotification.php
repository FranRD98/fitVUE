<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Bienvenido a fitVUE!')
            ->greeting('¡Hola'.($notifiable->name ? ' '.$notifiable->name : '').'!')
            ->line('Gracias por registrarte en fitVUE. Ya puedes empezar a crear tus rutinas, registrar tus dietas y seguir tu progreso.')
            ->action('Entrar en fitVUE', url('/'))
            ->line('Si tienes cualquier duda, responde a este correo.');
    }
}
