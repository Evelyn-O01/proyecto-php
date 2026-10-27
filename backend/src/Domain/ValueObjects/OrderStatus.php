<?php

namespace App\Backend\Domain\ValueObjects;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case SHIPPED = 'SHIPPED';
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagado',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-100 text-amber-800 border-amber-300',
            self::PAID => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::SHIPPED => 'bg-blue-100 text-blue-800 border-blue-300',
            self::DELIVERED => 'bg-purple-100 text-purple-800 border-purple-300',
            self::CANCELLED => 'bg-rose-100 text-rose-800 border-rose-300',
        };
    }
}
