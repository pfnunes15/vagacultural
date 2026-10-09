<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\EmailLog;
use App\Models\User;

/**
 * Sends the registration (email verification) message and records it in the
 * email log, so admins can consult what was sent and resend it.
 */
class RegistrationMailer
{
    public const TYPE = 'registration_verification';

    public function send(User $user, ?User $triggeredBy = null): EmailLog
    {
        if ($user->email_verified_at === null) {
            $user->sendEmailVerificationNotification();
        }

        return EmailLog::create([
            'user_id' => $user->id,
            'type' => self::TYPE,
            'recipient' => $user->email,
            'subject' => 'Confirma o teu registo na VAGA',
            'status' => 'sent',
            'sent_by' => $triggeredBy?->id,
            'sent_at' => now(),
        ]);
    }
}
