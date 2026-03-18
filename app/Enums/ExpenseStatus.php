<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Paid    = 'paid';
    case Partial = 'partial';
    case Unpaid  = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::Paid    => 'Paid',
            self::Partial => 'Partially Paid',
            self::Unpaid  => 'Unpaid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid    => 'success',
            self::Partial => 'warning',
            self::Unpaid  => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paid    => 'heroicon-o-check-circle',
            self::Partial => 'heroicon-o-clock',
            self::Unpaid  => 'heroicon-o-x-circle',
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
