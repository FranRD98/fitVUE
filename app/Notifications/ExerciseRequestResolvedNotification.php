<?php

namespace App\Notifications;

use App\Models\ExerciseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExerciseRequestResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(private ExerciseRequest $exerciseRequest)
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
        $approved = $this->exerciseRequest->status === 'approved';

        $message = (new MailMessage)
            ->subject($approved ? 'Tu ejercicio ha sido aprobado' : 'Tu ejercicio ha sido rechazado')
            ->greeting('¡Hola'.($notifiable->name ? ' '.$notifiable->name : '').'!');

        if ($approved) {
            $message
                ->line("Buenas noticias: tu propuesta \"{$this->exerciseRequest->name}\" ya forma parte del catálogo de ejercicios de fitVUE.")
                ->action('Ver catálogo', url('/'));
        } else {
            $message->line("Hemos revisado tu propuesta \"{$this->exerciseRequest->name}\" y de momento no la vamos a añadir al catálogo.");
        }

        return $message;
    }
}
