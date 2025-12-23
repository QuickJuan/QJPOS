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
    ];

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'default_location');
    }

    public function inventoryLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'inventory_location');
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
