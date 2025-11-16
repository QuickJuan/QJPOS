<?php

namespace App\Enums\TableRoomLocation;

use App\Traits\EnumsWithOptions;

enum LocationType: string
{
    use EnumsWithOptions;

    case DINE_IN = "dine-in";
    case TAKEOUT = "takeout";
}
