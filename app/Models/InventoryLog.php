<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    protected $fillable = [
        'inventory_location_id',
        'adjustment',
        'new_balance',
        'user_id',
        'approved_by',
    ];

    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'inventory_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
