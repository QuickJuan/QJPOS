<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'discount_name',
        'amount',
        'type',
        'remove_tax',
        'discount_type',
        'require_customer_info',
        'sort_order',
    ];

    protected $casts = [
        'amount'                => 'decimal:2',
        'remove_tax'            => 'boolean',
        'require_customer_info' => 'boolean',
    ];

    /**
     * Scope a query to only include active discounts
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->select([
            'id',
            'discount_name',
            'description',
            'amount',
            'type',
            'discount_type',
            'remove_tax',
            'require_customer_info',
            'created_at',
            'updated_at',
        ]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
