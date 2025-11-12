<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CashierSession extends Model
{
    protected $fillable = [
        'business_date',
        'cashier_id',
        'branch_id',
        'started_time',
        'closing_time',
        'beginning_cash',
        'closing_cash',
        'total_sales',
        'check_by',
        'cash_denomination',
        'meta_data',
        'notes',
    ];

    protected $casts = [
        'business_date'     => 'datetime',
        'started_time'      => 'datetime',
        'closing_time'      => 'datetime',
        'cash_denomination' => 'array',
        'meta_data'         => 'array',
    ];

    public function scopeOpenSession(Builder $query): void
    {
        $activeBranch = session('active_branch');

        if ($activeBranch) {
            $query->where('branch_id', $activeBranch->id)
                ->where('cashier_id', Auth::id())
                ->whereNull('closing_time');
        }
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function checkBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
