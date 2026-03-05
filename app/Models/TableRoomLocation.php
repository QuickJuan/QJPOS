<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableRoomLocation extends Model
{
    protected $fillable = [
        'name',
        'service_charge',
        'location_type',
        'service_charge_label',
        'service_charge_type',
    ];

    public function getServiceChargeLabelAttribute($value): string
    {
        return $value ?: 'Service Charge';
    }

    public function tableRooms(): HasMany
    {
        return $this->hasMany(TableRoom::class);
    }
}
