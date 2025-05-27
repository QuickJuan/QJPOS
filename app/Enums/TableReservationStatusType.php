<?php

namespace App\Enums;

use App\Traits\EnumsWithOptions;

enum TableReservationStatusType: string
{
    use EnumsWithOptions;

    case ACTIVE = 'active';
    case ARRIVED = 'archived';
    case CANCELLED = 'cancelled';
}
