<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    //

    protected $fillable = [
        'domain',
        'tenant_id',
    ];
    protected $casts = [
        'domain' => 'string',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
