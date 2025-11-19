<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Group extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'featured_image',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });

        static::updating(function ($group) {
            if ($group->isDirty('name')) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
