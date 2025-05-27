<?php

namespace App\Enums;

use App\Traits\EnumsWithOptions;

enum TableRoomStatusType: string
{
    use EnumsWithOptions;

    case VACANT = 'vacant';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
}
