<?php

namespace App\Filament\Tenant\Resources;

use App\Models\Page;
use Filament\Tables;
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
                            ->required()
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
                            ->live(),

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

                    DeleteAction::make(),

                    RestoreAction::make(),

                    ForceDeleteAction::make(),
                ])->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            PageResource\RelationManagers\SeoRelationManager::class,
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
}
