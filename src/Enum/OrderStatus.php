<?php

namespace App\Enum;

enum OrderStatus: int
{
    case EMPTY = 0;          // Пустой
    case UNPAID = 1;         // Не оплачен
    case PARTIALLY_PAID = 2; // Частично оплачен
    case OVERPAID = 3;       // Переплата
    case PAID = 4;           // Оплачен

    public function label(): string
    {
        return match($this) {
            self::EMPTY => 'Пустой',
            self::UNPAID => 'Не оплачен',
            self::PARTIALLY_PAID => 'Частично оплачен',
            self::OVERPAID => 'Переплата',
            self::PAID => 'Оплачен',
        };
    }
}