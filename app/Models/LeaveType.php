<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_paid',
        'default_days_per_year',
        'requires_document',
        'applies_to_regular',
        'applies_to_part_time',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_paid'              => 'boolean',
        'requires_document'    => 'boolean',
        'applies_to_regular'   => 'boolean',
        'applies_to_part_time' => 'boolean',
        'is_active'            => 'boolean',
        'default_days_per_year' => 'decimal:2',
        'sort_order'           => 'integer',
    ];

    public function leaveCredits(): HasMany
    {
        return $this->hasMany(EmployeeLeaveCredit::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
