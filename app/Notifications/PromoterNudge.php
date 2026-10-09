<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Encourages a promoter to publish more events.
 */
class PromoterNudge extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $promoterName) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Dá a conhecer os teus próximos eventos — VAGA')
            ->greeting('Olá ' . $this->promoterName . '!')
            ->line('Reparámos que não tens eventos agendados na VAGA.')
            ->line('Publica os teus próximos eventos e chega a quem procura cultura na Madeira.')
            ->action('Publicar um evento', route('dashboard.events.create'))
            ->line('[Cancelar estes e-mails](' . URL::signedRoute('unsubscribe', ['user' => $notifiable->id]) . ')');
    }
}
