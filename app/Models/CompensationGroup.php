<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompensationGroup extends Model
{
    protected $fillable = [
        'name',
        'applies_to',
        'color',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function compensationTypes(): HasMany
    {
        return $this->hasMany(CompensationType::class)->orderBy('sort_order');
    }
}
