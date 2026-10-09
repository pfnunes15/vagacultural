<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * Weekly "what you can't miss this week" digest, personalized per user.
 */
class WeeklyDigest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Event>  $events
     */
    public function __construct(public readonly Collection $events) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('O que não podes perder esta semana — VAGA')
            ->greeting('Olá ' . $notifiable->first_name . '!')
            ->line('Escolhemos estes eventos a pensar em ti:');

        foreach ($this->events as $event) {
            $mail->line('• ' . $event->title);
        }

        return $mail
            ->action('Ver a agenda', route('events.index'))
            ->line('Até breve na VAGA!')
            ->salutation('— Equipa VAGA')
            ->line('[Cancelar estes e-mails](' . URL::signedRoute('unsubscribe', ['user' => $notifiable->id]) . ')');
    }
}
