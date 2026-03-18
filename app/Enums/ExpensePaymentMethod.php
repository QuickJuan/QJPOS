<?php

namespace App\Enums;

enum ExpensePaymentMethod: string
{
    case Cash     = 'cash';
    case Bank     = 'bank';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::Cash     => 'Cash',
            self::Bank     => 'Bank Transfer',
            self::Purchase => 'Purchase (Payable)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cash     => 'success',
            self::Bank     => 'info',
            self::Purchase => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash     => 'heroicon-o-banknotes',
            self::Bank     => 'heroicon-o-building-library',
            self::Purchase => 'heroicon-o-credit-card',
        };
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
