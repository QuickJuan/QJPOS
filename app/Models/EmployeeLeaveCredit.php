<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveCredit extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
    ];

    protected $casts = [
        'year'       => 'integer',
        'total_days' => 'decimal:2',
        'used_days'  => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /** Remaining days available for paid leave */
    public function getRemainingDaysAttribute(): float
    {
        return max(0, (float) $this->total_days - (float) $this->used_days);
    }
}
