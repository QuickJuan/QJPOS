<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PageBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        /**
         * When a block is permanently deleted,
         * clean up any stored media files
         */
        static::forceDeleted(function ($block) {
            $block->deleteMediaFiles();
        });

        /**
         * When a block is soft deleted,
         * clean up media files
         */
        static::deleting(function ($block) {
            if (!$block->isForceDeleting()) {
                $block->deleteMediaFiles();
            }
        });
    }

    /**
     * Delete media files associated with this block
     */
    private function deleteMediaFiles(): void
    {
        if (!$this->content || !is_array($this->content)) {
            return;
        }

        $filePaths = [];

        // Extract file paths from content
        foreach ($this->content as $key => $value) {
            if (is_string($value) && $this->isFilePath($value)) {
                $filePaths[] = $value;
            } elseif ($key === 'images' && is_array($value)) {
                // Handle image repeaters
                foreach ($value as $image) {
                    if (is_array($image) && isset($image['image']) && $this->isFilePath($image['image'])) {
                        $filePaths[] = $image['image'];
                    }
                }
            } elseif (is_array($value)) {
                // Handle nested repeaters (products, reviews, etc.)
                foreach ($value as $item) {
                    if (is_array($item)) {
                        foreach ($item as $itemValue) {
                            if (is_string($itemValue) && $this->isFilePath($itemValue)) {
                                $filePaths[] = $itemValue;
                            }
                        }
                    }
                }
            }
        }

        // Delete files from storage
        foreach (array_unique($filePaths) as $filePath) {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }
    }

    /**
     * Check if a value is a file path
     */
    private function isFilePath($value): bool
    {
        return is_string($value) &&
               strpos($value, 'blocks/') === 0 &&
               !empty($value);
    }

    protected $fillable = [
        'page_id',
        'block_type_id',
        'order',
        'settings',
        'content',
        'visibility_settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'content' => 'array',
        'visibility_settings' => 'array',
    ];

    /**
     * Get the page this block belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the block type definition.
     */
    public function blockType(): BelongsTo
    {
        return $this->belongsTo(PageBlockType::class, 'block_type_id');
    }
}
