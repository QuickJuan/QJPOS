<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryUnitConversion extends Model
{
    protected $fillable = [
        'inventory_id',
        'unit_measure_id',
        'conversion_factor',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class);
    }
}
