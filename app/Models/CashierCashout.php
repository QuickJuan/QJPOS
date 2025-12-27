<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierCashout extends Model
{
    use HasFactory;

    public const TYPE_CASH_OUT = 'cash_out';
    public const TYPE_CASH_IN = 'cash_in';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'cashier_id',
        'cashier_session_id',
        'type',
        'amount',
        'source_name',
        'purpose',
        'details',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'meta' => 'array',
    ];

    protected $attributes = [
        'type' => self::TYPE_CASH_OUT,
        'status' => self::STATUS_PENDING,
    ];

    public static function availableTypes(): array
    {
        return [self::TYPE_CASH_OUT, self::TYPE_CASH_IN];
    }

    public static function availableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCashIn(): bool
    {
        return $this->type === self::TYPE_CASH_IN;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->isCashIn() ? 'Cash In' : 'Cash Out';
    }
}
