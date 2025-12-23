<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = [
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

}
