<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Attendance extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'user_id',
        'branch_id',
        'attendance_date',
        'schedule_timein',
        'schedule_timeout',
        'actual_timein',
        'actual_timeout',
    ];

    protected $casts = [
        'attendance_date'  => 'date',
        'schedule_timein'  => 'datetime',
        'schedule_timeout' => 'datetime',
        'actual_timein'    => 'datetime',
        'actual_timeout'   => 'datetime', 
    ];

    /**
     * Get the user that owns the attendance record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch where the attendance was recorded
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('clock_in_photos')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);

        $this->addMediaCollection('clock_out_photos')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }

    /**
     * Get clock in photo
     */
    public function getClockInPhotoAttribute()
    {
        return $this->getFirstMediaUrl('clock_in_photos');
    }

    /**
     * Get clock out photo
     */
    public function getClockOutPhotoAttribute()
    {
        return $this->getFirstMediaUrl('clock_out_photos');
    }
}
