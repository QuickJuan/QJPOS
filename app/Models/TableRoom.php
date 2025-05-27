<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableRoom extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'chairs',
        'with_timeframe',
        'merge_to',
        'status',
        'time_in',
        'time_out',
        'limit_hours',
        'table_width',
        'table_height',
        'table_x',
        'table_y',
    ];

    public function tableReservations(): HasMany
    {
        return $this->hasMany(TableReservation::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function mergeTo(): BelongsTo
    {
        return $this->belongsTo(TableRoom::class, 'merge_to');
    }
}
