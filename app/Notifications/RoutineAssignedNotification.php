<?php

namespace App\Notifications;

use App\Models\Routine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoutineAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private Routine $routine)
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
            ->subject('Tienes una nueva rutina asignada')
            ->greeting('¡Hola'.($notifiable->name ? ' '.$notifiable->name : '').'!')
            ->line("Tu entrenador te ha asignado la rutina \"{$this->routine->title}\".")
            ->line('Ya puedes consultarla y empezar a entrenar con ella.')
            ->action('Ver mi rutina', url('/'))
            ->line('¡A por ello!');
    }
}
