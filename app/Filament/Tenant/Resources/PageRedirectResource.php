<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\PageRedirectResource\Pages;
use App\Models\PageRedirect;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PageRedirectResource extends Resource
{
    protected static ?string $model = PageRedirect::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Redirects';
    protected static ?string $modelLabel      = 'Redirect';
    protected static ?string $pluralModelLabel = 'Redirects';
    protected static ?string $navigationGroup = 'Page Builder';
    protected static ?int    $navigationSort  = 11;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Redirect Rule')
                ->description('Define the old URL path and where it should redirect to.')
                ->schema([
                    TextInput::make('from_path')
                        ->label('From Path (Old URL)')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('/old-page-slug')
                        ->helperText('The old URL path visitors will land on. A leading slash is added automatically. Example: /about-us')
                        ->unique(PageRedirect::class, 'from_path', ignoreRecord: true),

                    TextInput::make('to_url')
                        ->label('To URL (Destination)')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('/new-about or https://example.com/page')
                        ->helperText('The destination URL or path. Can be relative (/new-slug) or absolute (https://...).'),

                    Select::make('redirect_type')
                        ->label('Redirect Type')
                        ->options([
                            301 => '301 — Permanent (SEO juice passes, recommended for moved pages)',
                            302 => '302 — Temporary (no SEO transfer, use for short-term redirects)',
                        ])
                        ->default(301)
                        ->required()
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive redirects are ignored by the site.'),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Internal Notes')
                        ->placeholder('Why was this redirect created? (optional)')
                        ->rows(2),
                ])->collapsed(),

            Section::make('How redirects work')
                ->schema([
                    Placeholder::make('info')
                        ->label('')
                        ->content(
                            'Redirects are checked for every GET request before pages are rendered. ' .
                            'A redirect for a path takes priority over a page with the same slug — ' .
                            'delete the redirect once the new page is live and the old one is gone. ' .
                            'The redirect map is cached for 10 minutes; changes take effect within that window.'
                        ),
                ])
                ->collapsed()
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_path')
                    ->label('From (Old URL)')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                TextColumn::make('to_url')
                    ->label('To (Destination)')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('success'),

                TextColumn::make('redirect_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (int $state) => $state === 301 ? 'primary' : 'warning')
                    ->formatStateUsing(fn (int $state) => $state === 301 ? '301 Permanent' : '302 Temporary'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All redirects'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('from_path');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPageRedirects::route('/'),
            'create' => Pages\CreatePageRedirect::route('/create'),
            'edit'   => Pages\EditPageRedirect::route('/{record}/edit'),
        ];
    }
}
