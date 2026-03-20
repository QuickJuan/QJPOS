<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplication extends Model
{
    protected $fillable = [
        'career_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'status',
    ];

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
