<?php

namespace App\Enums;

use App\Traits\EnumsWithOptions;

enum TableRoomStatusType: string
{
    use EnumsWithOptions;

    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
}
