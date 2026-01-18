<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\PageBlockResource\Pages;
use App\Models\PageBlock;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageBlockResource extends Resource
{
    protected static ?string $model = PageBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Page Blocks';

    protected static ?string $modelLabel = 'Block';

    protected static ?string $pluralModelLabel = 'Blocks';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationGroup = 'Page Builder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Block Information')
                    ->description('Configure this page block')
                    ->schema([
                        Hidden::make('page_id'),

                        Select::make('block_type_id')
                            ->relationship('blockType', 'name')
                            ->required()
                            ->disabled()
                            ->helperText('Block type cannot be changed after creation'),

                        Hidden::make('order')
                            ->default(0),
                    ]),

                Section::make('Block Content')
                    ->description('Edit block content and settings')
                    ->schema([
                        // This will be populated dynamically based on block type
                        // See the mutateFormDataBeforeCreate and mutateFormDataBeforeSave methods
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('blockType.name')
                    ->label('Block Type')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable()
                    ->width('80px'),

                TextColumn::make('page.title')
                    ->label('Page')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('block_type_id')
                    ->relationship('blockType', 'name'),

                Tables\Filters\SelectFilter::make('page_id')
                    ->relationship('page', 'title'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageBlocks::route('/'),
            'create' => Pages\CreatePageBlock::route('/create'),
            'edit' => Pages\EditPageBlock::route('/{record}/edit'),
        ];
    }
}
