<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'long_lat'        => 'string',
        'receipt_headers' => 'array', // Array of strings for receipt headers
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')
            ->withTimestamps();
    }
}
