<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSEO;

class SEOService
{
    /**
     * Generate meta data from page content.
     */
    public function generateMeta(Page $page): array
    {
        $title = $page->title;
        $description = $page->description ?? substr(strip_tags($page->content_json), 0, 160);

        return [
            'meta_title' => $title,
            'meta_description' => $description,
        ];
    }

    /**
     * Generate Open Graph data for social sharing.
     */
    public function generateOpenGraphData(Page $page): array
    {
        return [
            'og_title' => $page->title,
            'og_description' => $page->description,
            'og_image' => $page->featured_image,
            'og_url' => route('pages.show', $page->slug),
            'og_type' => 'website',
        ];
    }

    /**
     * Generate Twitter Card data.
     */
    public function generateTwitterCard(Page $page): array
    {
        return [
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $page->title,
            'twitter_description' => $page->description,
            'twitter_image' => $page->featured_image,
        ];
    }

    /**
     * Generate JSON-LD schema markup.
     */
    public function generateSchema(Page $page, string $type = 'Article'): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'headline' => $page->title,
            'description' => $page->description,
            'datePublished' => $page->published_at?->toIso8601String(),
            'dateModified' => $page->updated_at->toIso8601String(),
        ];

        if ($page->featured_image) {
            $schema['image'] = $page->featured_image;
        }

        if ($page->creator) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $page->creator->name,
            ];
        }

        return $schema;
    }

    /**
     * Calculate SEO score (0-100).
     */
    public function generateSeoScore(PageSEO $seo): int
    {
        $score = 0;

        // Meta title (15 points)
        if ($seo->meta_title) {
            $length = strlen($seo->meta_title);
            if ($length >= 50 && $length <= 60) {
                $score += 15;
            } elseif ($length >= 40 && $length <= 70) {
                $score += 10;
            } else {
                $score += 5;
            }
        }

        // Meta description (15 points)
        if ($seo->meta_description) {
            $length = strlen($seo->meta_description);
            if ($length >= 120 && $length <= 160) {
                $score += 15;
            } elseif ($length >= 100 && $length <= 180) {
                $score += 10;
            } else {
                $score += 5;
            }
        }

        // OG tags (20 points)
        if ($seo->og_title) {
            $score += 5;
        }
        if ($seo->og_description) {
            $score += 5;
        }
        if ($seo->og_image) {
            $score += 5;
        }

        // Schema (20 points)
        if ($seo->schema_json) {
            $score += 20;
        }

        // Twitter card (15 points)
        if ($seo->twitter_card) {
            $score += 15;
        }

        // Focus keywords (15 points)
        if ($seo->focus_keywords) {
            $score += 15;
        }

        return min($score, 100);
    }

    /**
     * Generate SERP and social preview data.
     */
    public function generatePreview(PageSEO $seo): array
    {
        return [
            'serp' => [
                'title' => $seo->meta_title ?? '',
                'description' => $seo->meta_description ?? '',
            ],
            'social_facebook' => [
                'title' => $seo->og_title,
                'description' => $seo->og_description,
                'image' => $seo->og_image,
            ],
            'social_twitter' => [
                'card' => $seo->twitter_card,
                'title' => $seo->twitter_title,
                'description' => $seo->twitter_description,
                'image' => $seo->twitter_image,
            ],
        ];
    }

    /**
     * Get SEO recommendations.
     */
    public function generateRecommendations(PageSEO $seo): array
    {
        $recommendations = [];

        // Meta title recommendations
        if (!$seo->meta_title) {
            $recommendations[] = [
                'level' => 'critical',
                'message' => 'Meta title is missing',
                'field' => 'meta_title',
            ];
        } elseif (strlen($seo->meta_title) < 50) {
            $recommendations[] = [
                'level' => 'warning',
                'message' => 'Meta title is shorter than recommended (50 chars)',
                'field' => 'meta_title',
            ];
        } elseif (strlen($seo->meta_title) > 70) {
            $recommendations[] = [
                'level' => 'warning',
                'message' => 'Meta title is longer than recommended (70 chars)',
                'field' => 'meta_title',
            ];
        }

        // Meta description recommendations
        if (!$seo->meta_description) {
            $recommendations[] = [
                'level' => 'critical',
                'message' => 'Meta description is missing',
                'field' => 'meta_description',
            ];
        } elseif (strlen($seo->meta_description) < 120) {
            $recommendations[] = [
                'level' => 'warning',
                'message' => 'Meta description is shorter than recommended (120 chars)',
                'field' => 'meta_description',
            ];
        } elseif (strlen($seo->meta_description) > 160) {
            $recommendations[] = [
                'level' => 'warning',
                'message' => 'Meta description is longer than recommended (160 chars)',
                'field' => 'meta_description',
            ];
        }

        // Focus keywords recommendations
        if (!$seo->focus_keywords) {
            $recommendations[] = [
                'level' => 'important',
                'message' => 'No focus keywords defined',
                'field' => 'focus_keywords',
            ];
        }

        // Schema recommendations
        if (!$seo->schema_json) {
            $recommendations[] = [
                'level' => 'important',
                'message' => 'No schema markup defined',
                'field' => 'schema_json',
            ];
        }

        // OG tags recommendations
        if (!$seo->og_title) {
            $recommendations[] = [
                'level' => 'recommended',
                'message' => 'OG title not set for better social sharing',
                'field' => 'og_title',
            ];
        }

        if (!$seo->og_image) {
            $recommendations[] = [
                'level' => 'recommended',
                'message' => 'OG image not set for better social sharing',
                'field' => 'og_image',
            ];
        }

        return $recommendations;
    }
}
