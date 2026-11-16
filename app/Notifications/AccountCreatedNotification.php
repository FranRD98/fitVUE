<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private string $createdByName)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Te han creado una cuenta en fitVUE')
            ->greeting('¡Hola'.($notifiable->name ? ' '.$notifiable->name : '').'!')
            ->line("{$this->createdByName} te ha dado de alta en fitVUE con el correo {$notifiable->email}.")
            ->line('Pide a tu entrenador la contraseña de acceso si no te la ha dado ya.')
            ->action('Entrar en fitVUE', url('/'))
            ->line('¡Bienvenido!');
    }
}
