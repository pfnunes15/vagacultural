<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Promoter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an applicant when an admin activates their promoter account.
 */
class PromoterActivated extends Notification
{
    use Queueable;

    public function __construct(public readonly Promoter $promoter) {}

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
            ->subject('O teu perfil de promotor foi ativado — VAGA')
            ->greeting('Boas notícias!')
            ->line('O teu perfil de promotor "' . $this->promoter->name . '" foi ativado por um administrador.')
            ->line('Já podes publicar eventos na agenda.')
            ->action('Ir para o painel', url('/dashboard/events'));
    }
}
