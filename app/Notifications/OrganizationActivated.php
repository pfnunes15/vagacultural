<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an applicant when an admin activates their account as an organization.
 */
class OrganizationActivated extends Notification
{
    use Queueable;

    public function __construct(public readonly Organization $organization) {}

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
            ->subject('A tua organização foi ativada — VAGA')
            ->greeting('Boas notícias!')
            ->line('A organização "' . $this->organization->name . '" foi ativada por um administrador.')
            ->line('Já podes gerir promotores e publicar eventos através deles.')
            ->action('Ir para o painel', url('/dashboard/events'));
    }
}
