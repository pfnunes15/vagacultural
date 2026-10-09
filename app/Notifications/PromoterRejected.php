<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an applicant when an admin rejects their promoter application.
 */
class PromoterRejected extends Notification
{
    use Queueable;

    public function __construct(public readonly ?string $reason = null) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Candidatura a promotor — VAGA')
            ->line('A tua candidatura a promotor não foi aprovada de momento.');

        if ($this->reason !== null && $this->reason !== '') {
            $message->line('Motivo: ' . $this->reason);
        }

        return $message->line('Podes candidatar-te novamente mais tarde.');
    }
}
