<?php

namespace App\Filament\Tenant\Pages;

use App\Enums\PageType;
use App\Http\Controllers\SitemapController;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page as FilamentPage;
use Illuminate\Support\Collection;

class SitemapPage extends FilamentPage
{
    protected static ?string $navigationIcon  = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Sitemap';
    protected static ?string $title           = 'Sitemap Generator';
    protected static ?string $navigationGroup = 'Page Builder';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.tenant.pages.sitemap-page';

    public function getSitemapUrl(): string
    {
        return url('/sitemap.xml');
    }

    public function getPages(): Collection
    {
        return Page::published()
            ->where(function ($q) {
                $q->whereNotNull('slug')
                  ->orWhere('page_type', PageType::LANDING_PAGE->value);
            })
            ->orderByRaw("FIELD(page_type, 'landing_page', 'page', 'blog', 'product', 'faq')")
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'title', 'slug', 'url_prefix', 'page_type', 'updated_at', 'published_at'])
            ->map(function (Page $page) {
                $path = $page->getFullUrlPath();
                return [
                    'title'      => $page->title ?? 'Landing Page',
                    'url'        => url($path ?: '/'),
                    'type'       => $page->page_type->label(),
                    'changefreq' => $this->changeFrequency($page->page_type),
                    'priority'   => $this->priority($page->page_type),
                    'lastmod'    => ($page->updated_at ?? $page->published_at)?->toDateString(),
                ];
            });
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerate_sitemap')
                ->label('Regenerate Sitemap')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    SitemapController::clearCache();
                    Notification::make()
                        ->title('Sitemap regenerated')
                        ->body('The cached sitemap has been cleared and will rebuild on next request.')
                        ->success()
                        ->send();
                }),

            Action::make('view_sitemap')
                ->label('View sitemap.xml')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url($this->getSitemapUrl())
                ->openUrlInNewTab(),
        ];
    }
}
