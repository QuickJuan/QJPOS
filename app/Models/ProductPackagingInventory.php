<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackagingInventory extends Model
{
    protected $table = 'inventory_product_packaging';

    protected $fillable = [
        'product_packaging_id',
        'inventory_id',
        'quantity',
        'unit_measure',
        'unit_type',
        'unit_reference_id',
    ];

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(InventoryPackaging::class, 'unit_reference_id');
    }

    public function unitConversion(): BelongsTo
    {
        return $this->belongsTo(UnitConversion::class, 'unit_reference_id');
    }
}
