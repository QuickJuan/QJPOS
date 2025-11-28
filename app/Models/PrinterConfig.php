<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrinterConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'bluetooth_name',
        'bluetooth_address',
        'service_uuid',
        'characteristic_uuid',
        'paper_size',
        'character_width',
        'is_active',
        'auto_cut',
        'cut_spacing',
        'print_categories',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_cut' => 'boolean',
        'cut_spacing' => 'integer',
        'character_width' => 'integer',
        'print_categories' => 'array',
    ];

    /**
     * Scope for active printers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for printer type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the printer configuration for a specific type
     */
    public static function getForType($type)
    {
        return static::active()->ofType($type)->first();
    }

    /**
     * Get character width based on paper size
     */
    public function getCharacterWidthAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Default widths based on paper size
        return $this->paper_size === '36mm' ? 32 : 48;
    }
}
