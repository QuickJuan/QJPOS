<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'terms',
        'amount_per_term',
        'start_deduction_date',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'amount_per_term'     => 'decimal:2',
        'start_deduction_date' => 'date',
        'approved_at'         => 'datetime',
        'terms'               => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CashAdvanceSchedule::class)->orderBy('term_number');
    }

    /** Total amount already paid across all schedule terms */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->schedules->sum(fn ($s) => (float) $s->paid_amount);
    }

    /** Outstanding balance = amount - total paid */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - $this->total_paid);
    }

    public static function statusOptions(): array
    {
        return [
            'pending'   => 'Pending',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'completed' => 'Completed',
        ];
    }
}
