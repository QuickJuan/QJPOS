<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageBlock;
use App\Services\PageBlockService;
use Illuminate\Http\Request;

class PageBlockController extends Controller
{
    public function __construct(
        private PageBlockService $blockService,
    ) {}

    /**
     * Store a newly created block.
     */
    public function store(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'block_type_id' => 'required|exists:page_block_types,id',
            'content' => 'nullable|array',
            'order' => 'required|integer',
        ]);

        $block = $this->blockService->addBlock(
            $page,
            $validated['block_type_id'],
            $validated['content'] ?? [],
            $validated['order']
        );

        return response()->json([
            'data' => $block->load('blockType'),
        ]);
    }

    /**
     * Update the specified block.
     */
    public function update(Request $request, PageBlock $block)
    {
        $this->authorize('update', $block->page);

        $validated = $request->validate([
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
            'order' => 'nullable|integer',
            'visibility_settings' => 'nullable|array',
        ]);

        $block = $this->blockService->updateBlock($block, $validated);

        return response()->json([
            'data' => $block,
        ]);
    }

    /**
     * Delete the specified block.
     */
    public function destroy(PageBlock $block)
    {
        $this->authorize('update', $block->page);

        $this->blockService->deleteBlock($block);

        return response()->json(['success' => true]);
    }

    /**
     * Reorder blocks on a page.
     */
    public function reorder(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'order' => 'required|array',
        ]);

        $this->blockService->reorderBlocks($page, $validated['order']);

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate a block.
     */
    public function duplicate(PageBlock $block)
    {
        $this->authorize('update', $block->page);

        $newBlock = $this->blockService->duplicateBlock($block);

        return response()->json([
            'data' => $newBlock->load('blockType'),
        ]);
    }
}
