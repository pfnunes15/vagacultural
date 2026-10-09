<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Pending => 'Pendente',
            self::Published => 'Publicado',
            self::Rejected => 'Rejeitado',
            self::Archived => 'Arquivado',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}
