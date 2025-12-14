<?php

namespace App\Enums;

enum PaymentType: string
{
    case CASH = 'cash';
    case CREDIT = 'credit';
    case CARD = 'card';
    case E_WALLET = 'e-wallet';
    case GIFT_CHECK = 'gift-check';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CREDIT => 'Credit',
            self::CARD => 'Card',
            self::E_WALLET => 'E-Wallet',
            self::GIFT_CHECK => 'Gift Check',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
