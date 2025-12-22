<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitMeasure extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'description',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryConversions(): HasMany
    {
        return $this->hasMany(InventoryUnitConversion::class);
    }
}
