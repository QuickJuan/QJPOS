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
        $query->where('cashier_id', Auth::id())
            // ->where('branch_id', session('active_branch')['id'])
            ->whereNull('closing_time');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function checkBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'check_by');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
