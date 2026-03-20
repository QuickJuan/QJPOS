<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompensationType extends Model
{
    protected $fillable = [
        'compensation_group_id',
        'code',
        'name',
        'type',
        'is_taxable',
        'is_mandatory',
        'is_employer_shared',
        'computation_type',
        'default_amount',
        'default_rate',
        'applies_to_regular',
        'applies_to_part_time',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_taxable'           => 'boolean',
        'is_mandatory'         => 'boolean',
        'is_employer_shared'   => 'boolean',
        'applies_to_regular'   => 'boolean',
        'applies_to_part_time' => 'boolean',
        'is_active'            => 'boolean',
        'default_amount'       => 'decimal:2',
        'default_rate'         => 'decimal:4',
        'sort_order'           => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CompensationGroup::class, 'compensation_group_id');
    }

    public function employeeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
    }

    /** Human-readable label for the type direction */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'income'    => 'Income / Benefit',
            'deduction' => 'Deduction',
            default     => ucfirst($this->type),
        };
    }
}
