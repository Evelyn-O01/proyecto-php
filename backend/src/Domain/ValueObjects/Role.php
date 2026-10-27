<?php

namespace App\Backend\Domain\ValueObjects;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case SELLER = 'SELLER';
    case CLIENT = 'CLIENT';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::SELLER => 'Vendedor',
            self::CLIENT => 'Cliente',
        };
    }
}
