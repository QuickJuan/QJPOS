<?php

namespace App\Enums;

use App\Traits\EnumsWithOptions;

enum PlanType: string
{

    use EnumsWithOptions;

    case FREE = 'free';
    case BASIC = 'basic';
    case STARTER = 'starter';
    case PREMIUM = 'premium';
    case ENTERPRISE = 'enterprise';

}       
