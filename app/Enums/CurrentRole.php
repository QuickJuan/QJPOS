<?php

namespace App\Enums;

enum CurrentRole: string
{
    case CASHIERING = 'cashiering';
    case ORDER_TAKING = 'order_taking';

    public function label(): string
    {
        return match ($this) {
            self::CASHIERING => 'Cashiering',
            self::ORDER_TAKING => 'Order Taking',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
