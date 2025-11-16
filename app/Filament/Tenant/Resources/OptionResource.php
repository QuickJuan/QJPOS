<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use App\Models\Option;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\OptionResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Tenant\Resources\OptionResource\RelationManagers\OptionItemsRelationManager;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('option_name')
                    ->label('Option Name')
                    ->required()
                    ->maxLength(255),

                // Repeater::make('productsPivot')
                //     ->label('Products')
                //     ->schema([
                //         Select::make('product_id')
                //             ->label('Product')
                //             ->options(\App\Models\Product::pluck('name', 'id'))
                //             ->required()
                //             ->searchable()
                //             ->preload()
                //             ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                //         TextInput::make('max_quantity')
                //             ->label('Max Quantity')
                //             ->numeric()
                //             ->minValue(1)
                //             ->default(1)
                //             ->required()
                //             ->helperText('Maximum quantity that can be selected for this option'),

                //         Toggle::make('is_default')
                //             ->label('Is Default')
                //             ->default(false)
                //             ->helperText('Set as default option for this product'),
                //     ])
                //     ->columns(3)
                //     ->reorderable(false)
                //     ->collapsible()
                //     ->itemLabel(fn (array $state): ?string => \App\Models\Product::find($state['product_id'])?->name ?? null)
                //     ->addActionLabel('Add Product')
                //     ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                //         return $data;
                //     })
                //     ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                //         return $data;
                //     }),

                // SpatieMediaLibraryFileUpload::make('featured_image')
                //     ->collection('featured_image')
                //     ->image()
                //     ->imageEditor(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // SpatieMediaLibraryImageColumn::make('featured_image')
                //     ->collection('featured_image')
                //     ->circular(),

                TextColumn::make('option_name')
                    ->label('Option Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('products.name')
                    ->label('Products')
                    ->listWithLineBreaks()
                    ->badge()
                    ->limitList(3)
                    ->expandableLimitedList(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            OptionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOptions::route('/'),
            'create' => Pages\CreateOption::route('/create'),
            'edit'   => Pages\EditOption::route('/{record}/edit'),
            'view'   => Pages\ViewOption::route('/{record}/view'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 7; // Second in group
    }
}
