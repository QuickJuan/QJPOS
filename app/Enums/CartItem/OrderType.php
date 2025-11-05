<?php
namespace App\Enums\CartItem;

use App\Traits\EnumsWithOptions;

enum OrderType: string
{
    use EnumsWithOptions;

    case DINEIN   = "dine-in";
    case TAKEOUT  = "takeout";
    case DELIVERY = "delivery";
}
