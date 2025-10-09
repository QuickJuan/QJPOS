<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory extends Model
{
    protected $fillable = [
        'name',
        'unit_measure',
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
}
