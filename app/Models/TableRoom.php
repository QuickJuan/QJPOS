<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableRoom extends Model  implements HasMedia
{

    use InteractsWithMedia;
    
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
            // ->usePathGenerator(new MediaPathGenerator());
    }

    public function getFeaturedImageUrl()
    {
        return $this->getFirstMediaUrl('featured_image');
    }


}
