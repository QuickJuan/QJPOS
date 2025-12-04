<?php 

namespace App\Enums\Order;

use App\Traits\EnumsWithOptions;

enum Status: string
{
    use EnumsWithOptions;

    case SETTLED = "settled";
    case REFUND  = "refund";
}