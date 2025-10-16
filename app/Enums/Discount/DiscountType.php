<?php
namespace App\Enums\Discount;

use App\Traits\EnumsWithOptions;

enum DiscountType: string
{
    use EnumsWithOptions;

    case REGULAR = "regular";
    case SPECIAL = "special";
    case SENIOR  = "senior";
    case PWD     = "pwd";
}
