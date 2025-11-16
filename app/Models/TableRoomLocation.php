<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableRoomLocation extends Model
{
    protected $fillable = [
        'name',
        'service_charge',
        'location_type'
    ];

    public function tableRooms(): HasMany
    {
        return $this->hasMany(TableRoom::class);
    }
}
