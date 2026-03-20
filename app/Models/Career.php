<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Career extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'department',
        'location',
        'employment_type',
        'summary',
        'description',
        'requirements',
        'responsibilities',
        'salary_range',
        'status',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * SEO-friendly slug: {department}-{title}-{id}
     * e.g. "it-front-end-developer-1"
     */
    public function getSlugAttribute(): string
    {
        $parts = array_filter([$this->department, $this->title]);
        return Str::slug(implode(' ', $parts)) . '-' . $this->id;
    }
}
