<?php

namespace App\Enums\TableRoomLocation;

use App\Traits\EnumsWithOptions;

enum ServiceChargeType: string
{
    use EnumsWithOptions;

    case AUTO = 'auto';
    case MANUAL = 'manual';
}
