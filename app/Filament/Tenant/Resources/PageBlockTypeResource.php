<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\PageBlockTypeResource\Pages;
use App\Models\PageBlockType;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageBlockTypeResource extends Resource
{
    protected static ?string $model = PageBlockType::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Block Types';

    protected static ?string $modelLabel = 'Block Type';

    protected static ?string $pluralModelLabel = 'Block Types';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Page Builder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Block Type Information')
                    ->description('Define a new page block type')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Banner, Text, Gallery')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (!$get('slug')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->unique(PageBlockType::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., banner, text, gallery')
                            ->helperText('URL-friendly identifier'),

                        TextInput::make('component_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., BannerBlock')
                            ->helperText('Vue component name for this block'),

                        TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('e.g., image, file-text, gallery')
                            ->helperText('Heroicon name (without prefix)'),

                        TextInput::make('category')
                            ->maxLength(255)
                            ->placeholder('e.g., hero, content, conversion')
                            ->helperText('Block category for organization'),

                        RichEditor::make('description')
                            ->maxLength(500)
                            ->placeholder('Description of what this block does')
                            ->helperText('Displayed in block library'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('category')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('component_name')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'hero' => 'Hero',
                        'content' => 'Content',
                        'conversion' => 'Conversion',
                        'social' => 'Social',
                        'media' => 'Media',
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
            'index' => Pages\ListPageBlockTypes::route('/'),
            'create' => Pages\CreatePageBlockType::route('/create'),
            'edit' => Pages\EditPageBlockType::route('/{record}/edit'),
        ];
    }
}
