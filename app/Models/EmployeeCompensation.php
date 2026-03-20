<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCompensation extends Model
{
    protected $fillable = [
        'employee_id',
        'compensation_type_id',
        'amount',
        'rate',
        'is_active',
        'effective_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'rate'           => 'decimal:4',
        'is_active'      => 'boolean',
        'effective_date' => 'date',
        'end_date'       => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function compensationType(): BelongsTo
    {
        return $this->belongsTo(CompensationType::class);
    }

    /**
     * Resolve the effective amount for this record.
     * Falls back to the compensation type default if no override is set.
     */
    public function getEffectiveAmountAttribute(): ?float
    {
        if ($this->amount !== null) {
            return (float) $this->amount;
        }

        return $this->compensationType?->default_amount !== null
            ? (float) $this->compensationType->default_amount
            : null;
    }

    /**
     * Resolve the effective rate for this record.
     */
    public function getEffectiveRateAttribute(): ?float
    {
        if ($this->rate !== null) {
            return (float) $this->rate;
        }

        return $this->compensationType?->default_rate !== null
            ? (float) $this->compensationType->default_rate
            : null;
    }
}
