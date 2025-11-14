<?php
namespace App\Enums\Receipt;

use App\Traits\EnumsWithOptions;

enum Type: string
{
    use EnumsWithOptions;

    case RECEIPT = "receipt";
    case BILL    = "bill";
    case KITCHEN = "kitchen";
}
