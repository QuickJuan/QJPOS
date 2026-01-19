<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBlockType extends Model
{
    use HasFactory;

    protected $table = 'page_block_types';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'category',
        'component_name',
        'schema_template',
        'settings_schema',
    ];

    protected $casts = [
        'schema_template' => 'array',
        'settings_schema' => 'array',
    ];

    public $timestamps = true;

    /**
     * Get the blocks of this type.
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class, 'block_type_id');
    }
}
