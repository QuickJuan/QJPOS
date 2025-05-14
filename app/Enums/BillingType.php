<?php

namespace App\Enums;

use App\Traits\EnumsWithOptions;

enum BillingType: string
{
    use EnumsWithOptions;

    case FREE = 'free';
    case TRANSACTIONAL = 'transactional';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}