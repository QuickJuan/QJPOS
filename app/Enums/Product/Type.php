<?php
namespace App\Enums\Product;

use App\Traits\EnumsWithOptions;

enum Type: string
{
    use EnumsWithOptions;

    case SIMPLE    = "simple";
    case COMPOSITE = "composite";
    case BUNDLE    = "bundle";
}
