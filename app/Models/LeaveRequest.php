<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_requested',
        'days_with_pay',
        'days_without_pay',
        'is_half_day',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'days_requested'   => 'decimal:2',
        'days_with_pay'    => 'decimal:2',
        'days_without_pay' => 'decimal:2',
        'is_half_day'      => 'boolean',
        'approved_at'      => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** True if any LWOP days will be deducted from salary */
    public function hasLwopAttribute(): bool
    {
        return (float) $this->days_without_pay > 0;
    }
}
