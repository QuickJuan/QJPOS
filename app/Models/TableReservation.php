<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableReservation extends Model
{
    protected $fillable = [
        'table_room_id',
        'user_id',
        'reservation_from',
        'reservation_to',
        'status',
        'notes',
        'pax',
        'name',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'reservation_from' => 'datetime',
        'reservation_to' => 'datetime',
        'status' => 'string',
        'notes' => 'string',
        'pax' => 'integer',
        'contact_phone' => 'string',
        'contact_email' => 'string',
    ];

    public function tableRoom(): BelongsTo
    {
        return $this->belongsTo(TableRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
