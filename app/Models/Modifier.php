<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modifier extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'list',
    ];

    protected $casts = [
        'list' => 'array',
    ];

    public function scopeWithMappedData(Builder $query)
    {
        return $query->get()
            ->map(fn($modifier) => [
                'id'   => $modifier->id,
                'name' => $modifier->name,
                'list' => $modifier->list ?? [],
            ]);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'modifier_product', 'modifier_id', 'product_id')->withTimestamps();
    }
}
