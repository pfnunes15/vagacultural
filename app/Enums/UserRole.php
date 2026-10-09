<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Organization = 'organization';
    case Promoter = 'promoter';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Organization => 'Organização',
            self::Promoter => 'Promotor',
            self::User => 'Utilizador',
        };
    }
}
