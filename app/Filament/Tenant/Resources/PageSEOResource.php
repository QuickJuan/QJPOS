<?php

namespace App\Filament\Tenant\Resources;

use App\Models\PageSEO;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Tenant\Resources\PageSEOResource\Pages as PageSEOPages;

class PageSEOResource extends Resource
{
    protected static ?string $model = PageSEO::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Page SEO';

    protected static ?string $modelLabel = 'Page SEO';

    protected static ?string $pluralModelLabel = 'Page SEO';

    protected static ?string $navigationGroup = 'Page Builder';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Page Selection')
                    ->description('Select the page to configure SEO for')
                    ->schema([
                        Forms\Components\Select::make('page_id')
                            ->label('Page')
                            ->relationship('page', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Each page can only have one SEO configuration')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                if ($record->page_type === 'landing_page') {
                                    return '🏠 Landing Page (Home)';
                                }
                                return $record->title;
                            }),
                    ]),

                Forms\Components\Section::make('Basic SEO')
                    ->description('Configure basic SEO metadata for search engines')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->placeholder('Enter SEO title (optimal: 50-60 characters)')
                            ->helperText('If empty, page title will be used for SEO'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('SEO Meta Description')
                            ->maxLength(160)
                            ->placeholder('Enter meta description (optimal: 150-160 characters)')
                            ->helperText('Brief description for search engines and social media')
                            ->rows(3),

                        Forms\Components\Textarea::make('meta_keywords')
                            ->label('SEO Keywords')
                            ->placeholder('keyword1, keyword2, keyword3')
                            ->helperText('Comma-separated keywords for SEO')
                            ->rows(2),

                        Forms\Components\TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->placeholder('https://example.com/page')
                            ->helperText('Specify the preferred URL if this page has duplicates (leave empty to auto-generate)'),

                        Forms\Components\Select::make('meta_robots')
                            ->label('Meta Robots')
                            ->options([
                                'index, follow' => 'Index, Follow (Default)',
                                'noindex, follow' => 'No Index, Follow',
                                'index, nofollow' => 'Index, No Follow',
                                'noindex, nofollow' => 'No Index, No Follow',
                            ])
                            ->default('index, follow')
                            ->helperText('Control how search engines crawl and index this page'),
                    ])->columns(2),

                Forms\Components\Section::make('Open Graph / Facebook')
                    ->description('Optimize how your page appears when shared on Facebook and other platforms')
                    ->schema([
                        Forms\Components\TextInput::make('og_title')
                            ->label('OG Title')
                            ->maxLength(60)
                            ->placeholder('Leave empty to use SEO title')
                            ->helperText('Title for social media sharing'),

                        Forms\Components\Textarea::make('og_description')
                            ->label('OG Description')
                            ->maxLength(160)
                            ->placeholder('Leave empty to use meta description')
                            ->helperText('Description for social media sharing')
                            ->rows(3),

                        Forms\Components\FileUpload::make('og_image')
                            ->label('OG Image')
                            ->image()
                            ->disk('public')
                            ->directory('pages/og-images')
                            ->visibility('public')
                            ->helperText('Recommended size: 1200x630px (leave empty to use featured image)'),
                    ])->columns(2)->collapsible(),

                Forms\Components\Section::make('Twitter Card')
                    ->description('Optimize how your page appears when shared on Twitter/X')
                    ->schema([
                        Forms\Components\Select::make('twitter_card')
                            ->label('Twitter Card Type')
                            ->options([
                                'summary' => 'Summary',
                                'summary_large_image' => 'Summary Large Image',
                            ])
                            ->default('summary_large_image')
                            ->helperText('Card type for Twitter/X sharing'),

                        Forms\Components\TextInput::make('twitter_title')
                            ->label('Twitter Title')
                            ->maxLength(60)
                            ->placeholder('Leave empty to use OG title')
                            ->helperText('Title for Twitter/X sharing'),

                        Forms\Components\Textarea::make('twitter_description')
                            ->label('Twitter Description')
                            ->maxLength(160)
                            ->placeholder('Leave empty to use OG description')
                            ->helperText('Description for Twitter/X sharing')
                            ->rows(3),

                        Forms\Components\FileUpload::make('twitter_image')
                            ->label('Twitter Image')
                            ->image()
                            ->disk('public')
                            ->directory('pages/twitter-images')
                            ->visibility('public')
                            ->helperText('Recommended size: 1200x675px (leave empty to use OG image)'),
                    ])->columns(2)->collapsible(),

                Forms\Components\Section::make('Structured Data (JSON-LD)')
                    ->description('Add structured data schema for rich search results')
                    ->schema([
                        Forms\Components\Select::make('schema_type')
                            ->label('Schema Type')
                            ->options([
                                'Article'             => 'Article',
                                'BlogPosting'         => 'Blog Posting',
                                'WebPage'             => 'Web Page',
                                'Product'             => 'Product',
                                'SoftwareApplication' => 'Software Application',
                                'LocalBusiness'       => 'Local Business',
                                'Organization'        => 'Organization',
                                'FAQPage'             => 'FAQ Page',
                            ])
                            ->placeholder('Select schema type')
                            ->helperText('Choose the appropriate schema type for this page'),

                        Forms\Components\Textarea::make('schema_json')
                            ->label('Extra Properties (JSON)')
                            ->placeholder('{"name": "My App", "applicationCategory": "BusinessApplication"}')
                            ->helperText('Optional: additional schema.org properties as a valid JSON object. @context and @type are added automatically from the Schema Type above.')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])->columns(1)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page.title')
                    ->label('Page')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->page?->page_type === 'landing_page') {
                            return '🏠 Landing Page (Home)';
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('meta_title')
                    ->label('SEO Title')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('meta_description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('has_og_image')
                    ->label('OG Image')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->og_image)),

                Tables\Columns\IconColumn::make('has_schema')
                    ->label('Schema')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->schema_type) || !empty($record->schema_json)),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('schema_type')
                    ->label('Schema Type')
                    ->options([
                        'Article' => 'Article',
                        'BlogPosting' => 'Blog Posting',
                        'WebPage' => 'Web Page',
                        'Product' => 'Product',
                        'LocalBusiness' => 'Local Business',
                        'Organization' => 'Organization',
                        'FAQPage' => 'FAQ Page',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => PageSEOPages\ListPageSEOS::route('/'),
            'create' => PageSEOPages\CreatePageSEO::route('/create'),
            'edit' => PageSEOPages\EditPageSEO::route('/{record}/edit'),
        ];
    }
}
