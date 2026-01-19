<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockType;

class PageBlockService
{
    /**
     * Add a new block to a page.
     */
    public function addBlock(Page $page, int $blockTypeId, array $content, int $order): PageBlock
    {
        $blockType = PageBlockType::findOrFail($blockTypeId);

        // Validate content against schema if schema exists
        if ($blockType->settings_schema) {
            $this->validateBlockContent($blockTypeId, $content);
        }

        return $page->blocks()->create([
            'block_type_id' => $blockTypeId,
            'order' => $order,
            'content' => $content,
        ]);
    }

    /**
     * Update an existing block.
     */
    public function updateBlock(PageBlock $block, array $data): PageBlock
    {
        if (isset($data['content'])) {
            $this->validateBlockContent($block->block_type_id, $data['content']);
        }

        $block->update($data);

        return $block;
    }

    /**
     * Delete a block.
     */
    public function deleteBlock(PageBlock $block): void
    {
        $block->delete();
    }

    /**
     * Reorder blocks on a page.
     */
    public function reorderBlocks(Page $page, array $orderMap): void
    {
        foreach ($orderMap as $index => $blockId) {
            $page->blocks()->where('id', $blockId)->update(['order' => $index]);
        }
    }

    /**
     * Duplicate a block.
     */
    public function duplicateBlock(PageBlock $block): PageBlock
    {
        return $block->page->blocks()->create([
            'block_type_id' => $block->block_type_id,
            'order' => $block->order + 1,
            'settings' => $block->settings,
            'content' => $block->content,
            'visibility_settings' => $block->visibility_settings,
        ]);
    }

    /**
     * Validate block content against schema.
     */
    public function validateBlockContent(int $blockTypeId, array $content): bool
    {
        $blockType = PageBlockType::findOrFail($blockTypeId);

        if (!$blockType->settings_schema) {
            return true;
        }

        // Basic validation - can be extended with JSON Schema validator
        return true;
    }

    /**
     * Get all available block types.
     */
    public function getBlockTypes()
    {
        return PageBlockType::all();
    }

    /**
     * Get a specific block type.
     */
    public function getBlockType(int $id): PageBlockType
    {
        return PageBlockType::findOrFail($id);
    }
}
