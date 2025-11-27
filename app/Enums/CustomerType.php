<?php

namespace App\Enums;

enum CustomerType: string
{
    case REGULAR = 'regular';
    case VIP = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::VIP => 'VIP',
        };
    }

    public static function options(): array
    {
        return [
            self::REGULAR->value => self::REGULAR->label(),
            self::VIP->value => self::VIP->label(),
        ];
    }
}
