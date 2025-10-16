<?php
namespace App\Enums\Discount;

use App\Traits\EnumsWithOptions;

enum TypeEnum: string
{
    use EnumsWithOptions;

    case FIXED      = "fixed";
    case PERCENTAGE = "percentage";
}
