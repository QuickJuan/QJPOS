<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TableRoom extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'branch_id',
        'table_room_location_id',
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
        'pax_limit',
        'screen_position',
        'dining_start',
        'dining_end',
        'notes',
        'featured_image',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
    }

    public function getFeaturedImageUrl(): string
    {
        return $this->getFirstMediaUrl('featured_image');
    }

    public function scopeActiveBranch(Builder $query): mixed
    {
        return $query->where('branch_id', session('active_branch')['id']);
    }

    public function scopeSelectedLocation(Builder $query, $locationId): Builder
    {
        if ($locationId) {
            return $query->when($locationId, fn($q) => $q->where('table_room_location_id', $locationId));
        }

        return $query;
    }


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

    public function tableRoomLocation(): BelongsTo
    {
        return $this->belongsTo(TableRoomLocation::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
