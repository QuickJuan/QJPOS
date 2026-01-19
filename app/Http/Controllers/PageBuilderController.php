<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageBuilderService;
use App\Services\PageBlockService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageBuilderController extends Controller
{
    public function __construct(
        private PageBuilderService $pageBuilderService,
        private PageBlockService $blockService,
    ) {}

    /**
     * Display a listing of pages.
     */
    public function index()
    {
        $pages = Page::paginate(20);

        return Inertia::render('PageBuilder/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return Inertia::render('PageBuilder/Create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages',
            'description' => 'nullable|string|max:500',
            'featured_image' => 'nullable|url',
        ]);

        $page = $this->pageBuilderService->createPage($validated);

        return redirect()->route('tenant.page-builder.edit', $page)
            ->with('success', 'Page created successfully');
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        $this->authorize('update', $page);

        $page = $this->pageBuilderService->getPageForEdit($page);
        $blockTypes = $this->blockService->getBlockTypes();

        return Inertia::render('PageBuilder/Edit', [
            'page' => $page,
            'blockTypes' => $blockTypes,
        ]);
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug,' . $page->id,
            'description' => 'nullable|string|max:500',
            'featured_image' => 'nullable|url',
        ]);

        $page = $this->pageBuilderService->updatePage($page, $validated);

        return back()->with('success', 'Page updated successfully');
    }

    /**
     * Delete the specified page from storage.
     */
    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        $this->pageBuilderService->deletePage($page);

        return redirect()->route('tenant.page-builder.index')
            ->with('success', 'Page deleted successfully');
    }

    /**
     * Publish the specified page.
     */
    public function publish(Page $page)
    {
        $this->authorize('update', $page);

        $this->pageBuilderService->publishPage($page);

        return back()->with('success', 'Page published successfully');
    }

    /**
     * Unpublish the specified page.
     */
    public function unpublish(Page $page)
    {
        $this->authorize('update', $page);

        $this->pageBuilderService->unpublishPage($page);

        return back()->with('success', 'Page unpublished');
    }

    /**
     * Duplicate the specified page.
     */
    public function duplicate(Page $page)
    {
        $this->authorize('create', Page::class);

        $newPage = $this->pageBuilderService->duplicatePage($page);

        return redirect()->route('tenant.page-builder.edit', $newPage)
            ->with('success', 'Page duplicated successfully');
    }
}
