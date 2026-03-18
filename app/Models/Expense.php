<?php

namespace App\Models;

use App\Enums\ExpensePaymentMethod;
use App\Enums\ExpenseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Expense extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'expense_category_id',
        'title',
        'description',
        'amount',
        'payment_method',
        'payee',
        'expense_date',
        'notes',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'expense_date'   => 'date',
        'payment_method' => ExpensePaymentMethod::class,
        'status'         => ExpenseStatus::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('expense_attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class)->orderBy('due_date');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function updateStatus(): void
    {
        $payments = $this->payments()->get();

        if ($this->payment_method !== ExpensePaymentMethod::Purchase) {
            $this->update(['status' => ExpenseStatus::Paid]);
            return;
        }

        if ($payments->isEmpty()) {
            $this->update(['status' => ExpenseStatus::Unpaid]);
            return;
        }

        $paidCount = $payments->where('is_paid', true)->count();

        if ($paidCount === 0) {
            $this->update(['status' => ExpenseStatus::Unpaid]);
        } elseif ($paidCount < $payments->count()) {
            $this->update(['status' => ExpenseStatus::Partial]);
        } else {
            $this->update(['status' => ExpenseStatus::Paid]);
        }
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->where('is_paid', true)->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->paid_amount);
    }
}
