<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Inventory extends Model
{
    protected $fillable = [
        'name',
        'unit_measure',
        'unit_measure_id',
        'cost',
        'default_location',
        'low_stock_threshold',
        'overstock_threshold',
    ];

    protected $casts = [
        'low_stock_threshold' => 'integer',
        'overstock_threshold' => 'integer',
    ];

    public function getTotalStockAttribute(): float
    {
        return (float) ($this->relationLoaded('locationStocks')
            ? $this->locationStocks->sum('current_stock')
            : $this->locationStocks()->sum('current_stock'));
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold > 0
            && $this->total_stock <= $this->low_stock_threshold;
    }

    public function isOverstock(): bool
    {
        return $this->overstock_threshold > 0
            && $this->total_stock >= $this->overstock_threshold;
    }

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'default_location');
    }

    public function inventoryLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'inventory_location');
    }

    public function locationStocks(): HasMany
    {
        return $this->hasMany(InventoryLocationStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class);
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(InventoryUnitConversion::class);
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(InventoryPackaging::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inventory_product')
            ->withPivot(['quantity', 'unit_measure', 'unit_type', 'unit_reference_id'])
            ->withTimestamps();
    }
}
