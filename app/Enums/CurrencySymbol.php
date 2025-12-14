<?php

namespace App\Enums;

enum CurrencySymbol: string
{
    case PESO = '₱';
    case USD = '$';
    case EURO = '€';
    case YEN = '¥';
    case POUND = '£';

    public function label(): string
    {
        return match ($this) {
            self::PESO => 'Philippine Peso (₱)',
            self::USD => 'US Dollar ($)',
            self::EURO => 'Euro (€)',
            self::YEN => 'Japanese Yen (¥)',
            self::POUND => 'British Pound (£)',
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

    public function getSymbol(): string
    {
        return $this->value;
    }
}
