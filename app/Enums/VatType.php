<?php

namespace App\Enums;

enum VatType: string
{
    case VAT = 'vat';
    case NON_VAT = 'non_vat';

    public function getLabel(): string
    {
        return match ($this) {
            self::VAT => 'VAT',
            self::NON_VAT => 'Non-VAT',
        };
    }
}
