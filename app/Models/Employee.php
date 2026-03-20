<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'employee_no',
        'position',
        'department',
        'employment_type',
        'date_hired',
        'date_regularized',
        'date_separated',
        'basic_salary',
        'daily_rate',
        'hourly_rate',
        'pay_frequency',
        'tax_status',
        'sss_no',
        'philhealth_no',
        'pagibig_no',
        'tin_no',
        'bank_name',
        'bank_account_no',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_hired'        => 'date',
        'date_regularized'  => 'date',
        'date_separated'    => 'date',
        'basic_salary'      => 'decimal:2',
        'daily_rate'        => 'decimal:2',
        'hourly_rate'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
    }

    public function activeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class)->where('is_active', true);
    }

    /** Income items (allowances, benefits) */
    public function incomeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class)
            ->whereHas('compensationType', fn ($q) => $q->where('type', 'income'));
    }

    /** Deduction items */
    public function deductionCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class)
            ->whereHas('compensationType', fn ($q) => $q->where('type', 'deduction'));
    }

    public function cashAdvances(): HasMany
    {
        return $this->hasMany(CashAdvance::class)->orderByDesc('created_at');
    }

    public function leaveCredits(): HasMany
    {
        return $this->hasMany(EmployeeLeaveCredit::class)->orderBy('year', 'desc');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class)->orderByDesc('start_date');
    }

    /** Full name via user relation */
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? "Employee #{$this->id}";
    }

    /** Employment type options for forms */
    public static function employmentTypeOptions(): array
    {
        return [
            'regular'       => 'Regular',
            'probationary'  => 'Probationary',
            'casual'        => 'Casual',
            'contractual'   => 'Contractual',
            'part_time'     => 'Part-Time',
            'seasonal'      => 'Seasonal',
        ];
    }

    /** BIR Tax status options (civil status + dependents) */
    public static function taxStatusOptions(): array
    {
        return [
            'S'   => 'S  – Single / Separated (no dependents)',
            'S1'  => 'S1 – Single, 1 dependent',
            'S2'  => 'S2 – Single, 2 dependents',
            'S3'  => 'S3 – Single, 3 or more dependents',
            'M'   => 'M  – Married (no dependents)',
            'M1'  => 'M1 – Married, 1 dependent',
            'M2'  => 'M2 – Married, 2 dependents',
            'M3'  => 'M3 – Married, 3 or more dependents',
            'ME'  => 'ME  – Married, spouse also employed (no dependents)',
            'ME1' => 'ME1 – Married, spouse also employed, 1 dependent',
            'ME2' => 'ME2 – Married, spouse also employed, 2 dependents',
            'ME3' => 'ME3 – Married, spouse also employed, 3 or more dependents',
        ];
    }
}
