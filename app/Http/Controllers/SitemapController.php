<?php

namespace App\Http\Controllers;

use App\Enums\PageType;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public const CACHE_KEY = 'sitemap_xml';
    public const CACHE_TTL = 3600; // 1 hour

    public function index(): Response
    {
        $cacheKey = self::CACHE_KEY . '_' . tenant('id');

        $xml = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->generate();
        });

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY . '_' . tenant('id'));
    }

    private function generate(): string
    {
        $pages = Page::published()
            ->where(function ($q) {
                $q->whereNotNull('slug')
                  ->orWhere('page_type', PageType::LANDING_PAGE->value);
            })
            ->get(['id', 'title', 'slug', 'url_prefix', 'page_type', 'updated_at', 'published_at']);

        $urls = [];

        foreach ($pages as $page) {
            $path = $page->getFullUrlPath();

            if ($path === null) {
                continue;
            }

            $urls[] = [
                'loc'        => url($path ?: '/'),
                'lastmod'    => ($page->updated_at ?? $page->published_at)?->toAtomString(),
                'changefreq' => $this->changeFrequency($page->page_type),
                'priority'   => $this->priority($page->page_type),
            ];
        }

        return $this->buildXml($urls);
    }

    private function changeFrequency(PageType $type): string
    {
        return match ($type) {
            PageType::LANDING_PAGE => 'weekly',
            PageType::BLOG         => 'weekly',
            PageType::PRODUCT      => 'weekly',
            default                => 'monthly',
        };
    }

    private function priority(PageType $type): string
    {
        return match ($type) {
            PageType::LANDING_PAGE => '1.0',
            PageType::BLOG         => '0.7',
            PageType::PRODUCT      => '0.7',
            default                => '0.8',
        };
    }

    private function buildXml(array $urls): string
    {
        $items = '';

        foreach ($urls as $url) {
            $loc        = htmlspecialchars($url['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $lastmod    = $url['lastmod'] ? "\n        <lastmod>{$url['lastmod']}</lastmod>" : '';
            $changefreq = "\n        <changefreq>{$url['changefreq']}</changefreq>";
            $priority   = "\n        <priority>{$url['priority']}</priority>";

            $items .= <<<XML

    <url>
        <loc>{$loc}</loc>{$lastmod}{$changefreq}{$priority}
    </url>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
{$items}
</urlset>
XML;
    }
}
