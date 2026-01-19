<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSEO;
use Illuminate\Support\Str;

class PageBuilderService
{
    /**
     * Create a new page.
     */
    public function createPage(array $data): Page
    {
        $data['slug'] ??= $this->generateSlug($data['title']);
        $data['created_by'] ??= auth()->id();

        $page = Page::create($data);

        // Create empty SEO record
        $page->seo()->create([]);

        return $page;
    }

    /**
     * Update an existing page.
     */
    public function updatePage(Page $page, array $data): Page
    {
        $page->update($data);

        return $page;
    }

    /**
     * Publish a page.
     */
    public function publishPage(Page $page): Page
    {
        $page->publish();

        return $page;
    }

    /**
     * Unpublish a page.
     */
    public function unpublishPage(Page $page): Page
    {
        $page->unpublish();

        return $page;
    }

    /**
     * Delete a page.
     */
    public function deletePage(Page $page): void
    {
        $page->delete();
    }

    /**
     * Generate a unique slug from a title.
     */
    public function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = 1;
        $originalSlug = $slug;

        while (Page::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Duplicate a page with all its blocks and SEO data.
     */
    public function duplicatePage(Page $page): Page
    {
        $newPage = $page->replicate();
        $newPage->slug = $this->generateSlug($page->title . ' (Copy)');
        $newPage->status = 'draft';
        $newPage->published_at = null;
        $newPage->save();

        // Duplicate blocks
        foreach ($page->blocks as $block) {
            $newPage->blocks()->create([
                'block_type_id' => $block->block_type_id,
                'order' => $block->order,
                'settings' => $block->settings,
                'content' => $block->content,
                'visibility_settings' => $block->visibility_settings,
            ]);
        }

        // Duplicate SEO
        if ($page->seo) {
            $newPage->seo()->create(
                $page->seo->toArray()
            );
        }

        return $newPage;
    }

    /**
     * Get page for editing with all relations.
     */
    public function getPageForEdit(Page $page): Page
    {
        return $page->load('blocks.blockType', 'seo');
    }

    /**
     * Get a published page with all relations.
     */
    public function getPageForPublicView(string $slug): Page
    {
        return Page::where('slug', $slug)
            ->published()
            ->with(['blocks.blockType', 'seo'])
            ->firstOrFail();
    }

    /**
     * Increment page view count.
     */
    public function incrementViewCount(Page $page): void
    {
        $page->increment('view_count');
    }
}
