<?php

namespace App\Filament\Tenant\Resources;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockType;
use App\Models\PageSEO;
use Filament\Tables;
use Filament\Forms;
use App\Enums\PageType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;

use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Notifications\Notification;
use App\Filament\Tenant\Resources\PageResource\Pages;
use App\Filament\Tenant\Resources\PageResource\RelationManagers\BlocksRelationManager;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'Page';

    protected static ?string $pluralModelLabel = 'Pages';

    protected static ?string $navigationGroup = 'Page Builder';

    protected static ?int $navigationSort = 1;

    /**
     * Use ID for route model binding instead of slug
     */
    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Page Information')
                    ->description('Basic page details and visibility')
                    ->schema([
                        TextInput::make('title')
                            ->maxLength(255)
                            ->placeholder('Enter page title')
                            ->helperText('Page title is used for SEO if no SEO title is set')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (!$get('slug') && $get('page_type') !== PageType::LANDING_PAGE->value) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Toggle::make('hide_title')
                            ->label('Hide Page Title')
                            ->helperText('Hide the title from displaying on the page (title will still be used for SEO)')
                            ->default(false)
                            ->inline(false),

                        TextInput::make('slug')
                            ->required(fn ($get) => $get('page_type') !== PageType::LANDING_PAGE->value)
                            ->unique(Page::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('page-slug')
                            ->helperText('URL-friendly identifier')
                            ->hidden(fn ($get) => $get('page_type') === PageType::LANDING_PAGE->value)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // If the slug is cleared, auto-promote this page to a Landing Page
                                // so it gets served at the root URL "/" instead of becoming unreachable
                                if (blank($state) && $get('page_type') !== PageType::LANDING_PAGE->value) {
                                    $set('page_type', PageType::LANDING_PAGE->value);
                                    $set('url_prefix', null);

                                    \Filament\Notifications\Notification::make()
                                        ->info()
                                        ->title('Switched to Landing Page')
                                        ->body('Slug cleared — page type automatically set to Landing Page. This page will be served at the root URL (/).')
                                        ->send();
                                }
                            }),

                        TextInput::make('url_prefix')
                            ->label('URL Prefix')
                            ->maxLength(255)
                            ->placeholder('e.g., respiratory, products, blogs')
                            ->helperText('Leave empty for root level (domain.com/slug) or add prefix (domain.com/prefix/slug)')
                            ->hidden(fn ($get) => $get('page_type') === PageType::LANDING_PAGE->value)
                            ->live(),

                        TextInput::make('full_url')
                            ->label('Page URL')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record, $get) {
                                if ($get('page_type') === PageType::LANDING_PAGE->value) {
                                    return '/';
                                }

                                $prefix = $get('url_prefix');
                                $slug = $get('slug');

                                if (!$slug) {
                                    return 'Enter a slug to see the URL';
                                }

                                return $prefix ? "/{$prefix}/{$slug}" : "/{$slug}";
                            })
                            ->helperText('This is the URL where your page will be accessible')
                            ->suffixIcon('heroicon-o-link')
                            ->extraAttributes(['class' => 'font-mono']),

                        Select::make('page_type')
                            ->label('Page Type')
                            ->options(PageType::options())
                            ->default(PageType::PAGE->value)
                            ->placeholder('Select page type')
                            ->helperText('Categorize your page')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Clear slug and url_prefix for landing pages
                                if ($state === PageType::LANDING_PAGE->value) {
                                    $set('slug', null);
                                    $set('url_prefix', null);
                                }

                                // If Landing Page is selected and status is published, warn user
                                if ($state === PageType::LANDING_PAGE->value && $get('status') === 'published') {
                                    $existingLanding = Page::where('page_type', PageType::LANDING_PAGE)
                                        ->where('status', 'published')
                                        ->where('id', '!=', $get('id'))
                                        ->exists();

                                    if ($existingLanding) {
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('Warning: Multiple Landing Pages')
                                            ->body('Only one Landing Page should be published at a time. Other landing pages will be ignored.')
                                            ->persistent()
                                            ->send();
                                    }
                                }
                            }),

                        Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Check if publishing a landing page when another exists
                                if ($state === 'published' && $get('page_type') === PageType::LANDING_PAGE->value) {
                                    $existingLanding = Page::where('page_type', PageType::LANDING_PAGE)
                                        ->where('status', 'published')
                                        ->where('id', '!=', $get('id'))
                                        ->exists();

                                    if ($existingLanding) {
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('Warning: Multiple Landing Pages')
                                            ->body('Only one Landing Page should be published at a time. Other landing pages will be ignored.')
                                            ->persistent()
                                            ->send();
                                    }
                                }
                            }),
                    ])->columns(2),

                Section::make('Media')
                    ->description('Page featured image and video')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->image()
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('pages/featured-images')
                            ->visibility('public')
                            ->helperText('Featured image for social sharing and page preview'),

                        TextInput::make('featured_video')
                            ->url()
                            ->placeholder('https://example.com/video.mp4')
                            ->helperText('Optional featured video URL'),
                    ])->columns(2),

                Section::make('SEO Settings')
                    ->description('Search engine optimisation metadata for this page')
                    ->relationship('seo')
                    ->schema([
                        Section::make('Basic SEO')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('SEO Title')
                                    ->maxLength(60)
                                    ->placeholder('Enter SEO title (optimal: 50-60 characters)')
                                    ->helperText('If empty, the page title will be used'),

                                Textarea::make('meta_description')
                                    ->label('Meta Description')
                                    ->maxLength(160)
                                    ->placeholder('Enter meta description (optimal: 150-160 characters)')
                                    ->helperText('Brief description for search engines and social media')
                                    ->rows(3),

                                Textarea::make('meta_keywords')
                                    ->label('Keywords')
                                    ->placeholder('keyword1, keyword2, keyword3')
                                    ->helperText('Comma-separated keywords')
                                    ->rows(2),

                                TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->placeholder('https://example.com/page')
                                    ->helperText('Leave empty to auto-generate'),

                                Select::make('meta_robots')
                                    ->label('Meta Robots')
                                    ->options([
                                        'index, follow'     => 'Index, Follow (default)',
                                        'noindex, follow'   => 'No Index, Follow',
                                        'index, nofollow'   => 'Index, No Follow',
                                        'noindex, nofollow' => 'No Index, No Follow',
                                    ])
                                    ->default('index, follow'),
                            ])->columns(2),

                        Section::make('Open Graph / Social Sharing')
                            ->description('Controls how this page appears when shared on Facebook, LinkedIn, etc.')
                            ->schema([
                                TextInput::make('og_title')
                                    ->label('OG Title')
                                    ->maxLength(60)
                                    ->placeholder('Leave empty to use SEO title'),

                                Textarea::make('og_description')
                                    ->label('OG Description')
                                    ->maxLength(160)
                                    ->placeholder('Leave empty to use meta description')
                                    ->rows(3),

                                FileUpload::make('og_image')
                                    ->label('OG Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('pages/og-images')
                                    ->visibility('public')
                                    ->helperText('Recommended: 1200×630px'),
                            ])->columns(2)->collapsed(),

                        Section::make('Twitter / X Card')
                            ->description('Controls how this page appears when shared on Twitter / X.')
                            ->schema([
                                Select::make('twitter_card')
                                    ->label('Card Type')
                                    ->options([
                                        'summary'             => 'Summary',
                                        'summary_large_image' => 'Summary Large Image',
                                    ])
                                    ->default('summary_large_image'),

                                TextInput::make('twitter_title')
                                    ->label('Twitter Title')
                                    ->maxLength(60)
                                    ->placeholder('Leave empty to use OG title'),

                                Textarea::make('twitter_description')
                                    ->label('Twitter Description')
                                    ->maxLength(160)
                                    ->placeholder('Leave empty to use OG description')
                                    ->rows(3),

                                FileUpload::make('twitter_image')
                                    ->label('Twitter Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('pages/twitter-images')
                                    ->visibility('public')
                                    ->helperText('Recommended: 1200×675px'),
                            ])->columns(2)->collapsed(),

                        Section::make('Structured Data (JSON-LD)')
                            ->description('Add structured data for rich search results')
                            ->schema([
                                Select::make('schema_type')
                                    ->label('Schema Type')
                                    ->options([
                                        'Article'              => 'Article',
                                        'BlogPosting'          => 'Blog Posting',
                                        'WebPage'              => 'Web Page',
                                        'Product'              => 'Product',
                                        'SoftwareApplication'  => 'Software Application',
                                        'LocalBusiness'        => 'Local Business',
                                        'Organization'         => 'Organization',
                                        'FAQPage'              => 'FAQ Page',
                                    ])
                                    ->placeholder('Select schema type'),

                                Textarea::make('schema_json')
                                    ->label('Extra Properties (JSON)')
                                    ->placeholder('{"name": "My App", "applicationCategory": "BusinessApplication"}')
                                    ->rows(4)
                                    ->helperText('Optional: additional schema.org properties as a valid JSON object. @context and @type are added automatically from the Schema Type above.'),
                            ])->columns(2)->collapsed(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->page_type === 'landing_page' ? 'Landing Page' : 'Untitled')),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->color('gray')
                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->page_type === 'landing_page' ? '/' : '—')),

                BadgeColumn::make('status')
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                    ])
                    ->sortable(),

                TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('blocks_count')
                    ->label('Blocks')
                    ->counts('blocks')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),

                    Tables\Actions\Action::make('preview')
                        ->icon('heroicon-o-eye')
                        ->url(function (Page $record) {
                            if ($record->page_type === 'landing_page') {
                                return '/';
                            }

                            $path = $record->getFullUrlPath();
                            return $path ? "/{$path}" : null;
                        })
                        ->visible(fn (Page $record) => $record->slug || $record->page_type === 'landing_page')
                        ->openUrlInNewTab()
                        ->tooltip('Preview how this page looks'),

                    Tables\Actions\Action::make('publish')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->hidden(fn (Page $record) => $record->isPublished())
                        ->action(fn (Page $record) => $record->publish())
                        ->successNotificationTitle('Page published'),

                    Tables\Actions\Action::make('unpublish')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->hidden(fn (Page $record) => !$record->isPublished())
                        ->action(fn (Page $record) => $record->unpublish())
                        ->successNotificationTitle('Page unpublished'),

                    Tables\Actions\Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Page $record) {
                            $service = app(\App\Services\PageBuilderService::class);
                            $newPage = $service->duplicatePage($record);
                            redirect(route('filament.tenant.resources.pages.edit', ['record' => $newPage]))->send();
                        })
                        ->successNotificationTitle('Page duplicated'),

                    Tables\Actions\Action::make('export')
                        ->label('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn (Page $record) => static::exportPage($record)),

                    DeleteAction::make(),

                    RestoreAction::make(),

                    ForceDeleteAction::make(),
                ])->button(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importPages')
                    ->label('Import Pages')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        Forms\Components\Tabs::make('Import Method')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('File Upload')
                                    ->schema([
                                        Forms\Components\FileUpload::make('import_file')
                                            ->label('Select Pages Export File')
                                            ->acceptedFileTypes(['application/json', 'text/plain'])
                                            ->maxSize(5120)
                                            ->disk('local')
                                            ->directory('imports')
                                            ->helperText('Upload a .json file exported from another site (max 5MB)'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Paste JSON')
                                    ->schema([
                                        Forms\Components\Textarea::make('import_json')
                                            ->label('Paste JSON Content')
                                            ->placeholder('Paste the exported JSON content here...')
                                            ->rows(10)
                                            ->helperText('Copy and paste the JSON from an exported file'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->modalWidth('4xl')
                    ->action(function (array $data) {
                        $hasFile = !empty($data['import_file']);
                        $hasJson = !empty($data['import_json']) && trim($data['import_json']) !== '';

                        if (!$hasFile && !$hasJson) {
                            Notification::make()->title('Validation Error')->body('Please upload a JSON file or paste JSON content.')->danger()->send();
                            return;
                        }

                        if ($hasFile) {
                            $filePath = is_array($data['import_file']) ? reset($data['import_file']) : $data['import_file'];
                            $content = null;
                            foreach (['imports/' . $filePath, 'livewire-tmp/' . $filePath, $filePath] as $path) {
                                try {
                                    $content = \Storage::disk('local')->get($path);
                                    if ($content) break;
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }
                            if (!$content) {
                                Notification::make()->title('File Error')->body('Could not read the uploaded file.')->danger()->send();
                                return;
                            }
                            static::importPagesFromContent($content);
                        } else {
                            static::importPagesFromContent($data['import_json']);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportPages')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn ($records) => static::exportMultiplePages($records)),
                    Tables\Actions\DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BlocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function exportPage(Page $page): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toISOString(),
            'pages' => [static::serializePage($page)],
        ];
        $filename = 'page-export-' . ($page->slug ?: 'landing') . '-' . now()->format('Y-m-d-H-i-s') . '.json';
        return response()->streamDownload(
            fn () => print json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public static function exportMultiplePages($pages): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toISOString(),
            'pages' => $pages->map(fn ($p) => static::serializePage($p))->values()->toArray(),
        ];
        $filename = 'pages-export-' . $pages->count() . '-pages-' . now()->format('Y-m-d-H-i-s') . '.json';
        return response()->streamDownload(
            fn () => print json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public static function serializePage(Page $page): array
    {
        $blocks = $page->blocks()->with('blockType')->orderBy('order')->get()->map(fn ($block) => [
            'block_type_slug' => $block->blockType?->slug,
            'order' => $block->order,
            'content' => $block->content,
            'settings' => $block->settings,
            'visibility_settings' => $block->visibility_settings,
        ])->toArray();

        $seo = $page->seo;

        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'url_prefix' => $page->url_prefix,
            'page_type' => $page->page_type instanceof \App\Enums\PageType ? $page->page_type->value : $page->page_type,
            'status' => $page->status,
            'hide_title' => $page->hide_title,
            'featured_image' => null,
            'featured_video' => $page->featured_video,
            'content_json' => $page->content_json,
            'blocks' => $blocks,
            'seo' => $seo ? [
                'meta_title' => $seo->meta_title,
                'meta_description' => $seo->meta_description,
                'focus_keywords' => $seo->focus_keywords,
                'meta_robots' => $seo->meta_robots,
                'og_title' => $seo->og_title,
                'og_description' => $seo->og_description,
                'og_image' => null,
                'twitter_card' => $seo->twitter_card,
                'twitter_title' => $seo->twitter_title,
                'twitter_description' => $seo->twitter_description,
                'twitter_image' => null,
                'schema_type' => $seo->schema_type,
                'schema_json' => $seo->schema_json,
                'canonical_url' => $seo->canonical_url,
            ] : null,
        ];
    }

    public static function importPagesFromContent(string $jsonContent): void
    {
        try {
            $importData = json_decode($jsonContent, true);
            if (!$importData || !isset($importData['pages'])) {
                throw new \Exception('Invalid format: missing "pages" array');
            }
            $imported = 0;
            $skipped = 0;
            $errors = [];
            foreach ($importData['pages'] as $pageData) {
                try {
                    static::importSinglePage($pageData);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "'{$pageData['title']}': " . $e->getMessage();
                }
            }
            Notification::make()
                ->title('Import Complete')
                ->body("{$imported} page(s) imported" . ($skipped ? ", {$skipped} skipped" : ''))
                ->success()
                ->send();
            if (!empty($errors)) {
                Notification::make()
                    ->title('Import Warnings')
                    ->body(implode("\n", array_slice($errors, 0, 5)))
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()->title('Import Failed')->body($e->getMessage())->danger()->send();
            \Log::error('Page import failed', ['error' => $e->getMessage()]);
        }
    }

    public static function importSinglePage(array $pageData): Page
    {
        $pageType = $pageData['page_type'] ?? PageType::PAGE->value;
        $existing = Page::withTrashed()
            ->where('slug', $pageData['slug'] ?? '')
            ->where('page_type', $pageType)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $page = $existing;
        } else {
            $page = new Page();
        }

        $page->fill([
            'title' => $pageData['title'],
            'slug' => $pageData['slug'] ?? null,
            'url_prefix' => $pageData['url_prefix'] ?? null,
            'page_type' => $pageType,
            'status' => $pageData['status'] ?? 'draft',
            'hide_title' => $pageData['hide_title'] ?? false,
            'featured_video' => $pageData['featured_video'] ?? null,
            'content_json' => $pageData['content_json'] ?? null,
        ]);
        $page->save();

        if (!empty($pageData['blocks'])) {
            $page->blocks()->forceDelete();
            foreach ($pageData['blocks'] as $blockData) {
                if (empty($blockData['block_type_slug'])) continue;
                $blockType = PageBlockType::where('slug', $blockData['block_type_slug'])->first();
                if (!$blockType) continue;
                $page->blocks()->create([
                    'block_type_id' => $blockType->id,
                    'order' => $blockData['order'] ?? 0,
                    'content' => $blockData['content'] ?? [],
                    'settings' => $blockData['settings'] ?? [],
                    'visibility_settings' => $blockData['visibility_settings'] ?? [],
                ]);
            }
        }

        if (!empty($pageData['seo'])) {
            $seoData = array_filter($pageData['seo'], fn ($v) => $v !== null);
            if (!empty($seoData)) {
                $page->seo()->updateOrCreate(['page_id' => $page->id], $seoData);
            }
        }

        return $page;
    }
}
