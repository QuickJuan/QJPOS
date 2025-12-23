<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'location',
    ];

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'inventory_location');
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryLocationStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class);
    }
}
