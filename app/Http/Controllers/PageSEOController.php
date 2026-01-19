<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SEOService;
use Illuminate\Http\Request;

class PageSEOController extends Controller
{
    public function __construct(
        private SEOService $seoService,
    ) {}

    /**
     * Get SEO data for a page.
     */
    public function show(Page $page)
    {
        $this->authorize('view', $page);

        return response()->json([
            'data' => $page->seo,
        ]);
    }

    /**
     * Update SEO data for a page.
     */
    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'focus_keywords' => 'nullable|string|max:255',
            'meta_robots' => 'nullable|string',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:300',
            'og_image' => 'nullable|url',
            'twitter_card' => 'nullable|in:summary,summary_large_image',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:200',
            'twitter_image' => 'nullable|url',
            'schema_type' => 'nullable|string',
            'schema_json' => 'nullable|json',
            'canonical_url' => 'nullable|url',
        ]);

        $seo = $page->seo;
        $seo->update($validated);

        // Generate schema if not provided
        if (!$seo->schema_json && !$validated['schema_json']) {
            $seo->schema_json = $this->seoService->generateSchema($page, $validated['schema_type'] ?? 'Article');
            $seo->save();
        }

        $score = $this->seoService->generateSeoScore($seo);
        $preview = $this->seoService->generatePreview($seo);
        $recommendations = $this->seoService->generateRecommendations($seo);

        return response()->json([
            'data' => $seo,
            'score' => $score,
            'preview' => $preview,
            'recommendations' => $recommendations,
        ]);
    }
}
