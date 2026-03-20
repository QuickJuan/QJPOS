<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestOrder extends Model
{
    protected $fillable = [
        'reference_no',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'order_type',
        'notes',
        'status',
        'total_amount',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(GuestOrderItem::class);
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'GO-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
        } while (static::where('reference_no', $ref)->exists());

        return $ref;
    }
}
