<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanUpgradedNotification extends Notification
{
    use Queueable;

    private const PLAN_NAMES = [
        2 => 'Premium',
        3 => 'Pro',
    ];

    public function __construct(private int $planId)
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
        $planName = self::PLAN_NAMES[$this->planId] ?? 'nuevo';

        return (new MailMessage)
            ->subject("Tu plan {$planName} ya está activo")
            ->greeting('¡Hola'.($notifiable->name ? ' '.$notifiable->name : '').'!')
            ->line("Hemos confirmado tu pago y tu cuenta ya es {$planName}.")
            ->line('A partir de ahora tienes acceso a todas las ventajas de este plan.')
            ->action('Ver mi cuenta', url('/'))
            ->line('Gracias por confiar en fitVUE.');
    }
}
