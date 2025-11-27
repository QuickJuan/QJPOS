<?php
namespace App\Services;

use App\Enums\Receipt\Type;
use App\Models\ReceiptFooter;

class ReceiptFooterService
{
    public function getReceiptFooter(string $type = Type::RECEIPT->value)
    {
        return ReceiptFooter::where('type', $type)->first();
    }
}
