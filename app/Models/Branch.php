<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'long_lat' => 'string',
    ];
}
