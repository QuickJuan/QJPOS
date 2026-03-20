<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'start_at',
        'end_at',
        'user_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Color per category for calendar display */
    public function categoryColor(): string
    {
        return match ($this->category) {
            'reservation' => '#f97316', // orange
            'meeting'     => '#3b82f6', // blue
            'reminder'    => '#a855f7', // purple
            'holiday'     => '#22c55e', // green
            default       => '#64748b',
        };
    }
}
