<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\NavigationItemResource\Pages;
use App\Models\NavigationItem;
use App\Models\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Navigation';

    protected static ?string $navigationGroup = 'Page Builder';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Navigation Item')
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(60)
                            ->placeholder('Home, About Us, Contact'),

                        Select::make('page_id')
                            ->label('Link to Page')
                            ->relationship('page', 'title')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                if ($record->page_type === 'landing_page') {
                                    return $record->title ?: 'Landing Page (Home)';
                                }
                                return $record->title ?: "Untitled Page (ID: {$record->id})";
                            })
                            ->preload(20)
                            ->searchable()
                            ->placeholder('Select a page (optional)')
                            ->helperText('If selected, URL will be auto-generated from the page'),

                        TextInput::make('url')
                            ->label('Custom URL')
                            ->maxLength(255)
                            ->placeholder('/custom-link or https://external.com')
                            ->helperText('Only used if no page is selected')
                            ->hidden(fn ($get) => $get('page_id') !== null),

                        Select::make('target')
                            ->options([
                                '_self' => 'Same Window',
                                '_blank' => 'New Window',
                            ])
                            ->default('_self')
                            ->required(),

                        Select::make('parent_id')
                            ->label('Parent Item')
                            ->relationship('parent', 'label')
                            ->placeholder('None (Top Level)')
                            ->preload(20)
                            ->searchable()
                            ->helperText('Create dropdown menus by setting a parent'),

                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-home')
                            ->helperText('Optional Heroicon class'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Hide navigation items without deleting them'),

                        Toggle::make('auth_only')
                            ->label('Authenticated Users Only')
                            ->default(false)
                            ->helperText('Only show this item to logged-in users'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('page.title')
                    ->label('Linked Page')
                    ->sortable()
                    ->default('—'),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->default('—'),

                TextColumn::make('parent.label')
                    ->label('Parent')
                    ->default('—'),

                TextColumn::make('order')
                    ->sortable()
                    ->width('80px'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('auth_only')
                    ->label('Auth Only')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'label')
                    ->label('Parent Item'),
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
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavigationItems::route('/'),
            'create' => Pages\CreateNavigationItem::route('/create'),
            'edit' => Pages\EditNavigationItem::route('/{record}/edit'),
        ];
    }
}
