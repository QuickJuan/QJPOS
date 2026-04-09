<?php

namespace App\Models;

use App\Enums\CustomerFeedbackStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CustomerFeedback extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'order_id',
        'invoice_no',
        'name',
        'email',
        'rating',
        'message',
        'status',
        'meta',
    ];

    protected $casts = [
        'rating' => 'integer',
        'meta' => 'array',
        'status' => CustomerFeedbackStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('photos')
            ->useDisk('public');
    }
}
