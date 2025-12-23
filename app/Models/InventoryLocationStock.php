<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLocationStock extends Model
{
    protected $table = 'inventory_location_stocks';

    protected $fillable = [
        'inventory_id',
        'location_id',
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
