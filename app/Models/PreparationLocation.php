<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreparationLocation extends Model
{
    protected $fillable = [
        'description',
        'printable',
        'show_on_screen',
    ];

    protected $casts = [
        'printable'      => 'boolean',
        'show_on_screen' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
