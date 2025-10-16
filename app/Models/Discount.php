<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'discount_name',
        'amount',
        'type',
        'remove_tax',
        'discount_type',
        'require_customer_info',
    ];

    protected $casts = [
        'remove_tax'            => 'boolean',
        'require_customer_info' => 'boolean',
    ];
}
