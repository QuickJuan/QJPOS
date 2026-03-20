<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleType extends Model
{
    protected $fillable = [
        'name',
        'expected_time_in',
        'duration_hours',
        'color',
        'description',
    ];

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    /**
     * Human-readable expected time in (e.g. "6:00 AM")
     */
    public function getFormattedTimeInAttribute(): string
    {
        return \Carbon\Carbon::createFromTimeString($this->expected_time_in)->format('g:i A');
    }
}
