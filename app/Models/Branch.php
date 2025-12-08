<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'branch_code',
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'long_lat',
        'is_active',
        'tin',
        'registration_number',
        'or_number',
        'bill_no',
        'order_number',
        'receipt_headers',
        'receipt_footer',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'long_lat'        => 'string',
        'receipt_headers' => 'array', // Array of strings for receipt headers
        'receipt_footer'  => 'array', // Array of strings for receipt footer
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')
            ->withTimestamps();
    }

    public function tableRooms(): HasMany
    {
        return $this->hasMany(TableRoom::class);
    }
}
