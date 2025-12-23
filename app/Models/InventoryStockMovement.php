<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockMovement extends Model
{
    protected $fillable = [
        'inventory_id',
        'location_id',
        'movement_type',
        'quantity',
        'unit_type',
        'unit_reference_id',
        'resulting_stock',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'resulting_stock' => 'decimal:4',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(InventoryPackaging::class, 'unit_reference_id');
    }
}
