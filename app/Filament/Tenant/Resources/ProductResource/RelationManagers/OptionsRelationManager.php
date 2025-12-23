<?php
namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use App\Filament\Tenant\Resources\OptionResource\RelationManagers\OptionItemsRelationManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship      = 'options';
    protected static ?string $label            = 'Options';
    protected static ?string $modelLabel       = 'Option';
    protected static ?string $pluralModelLabel = 'Options';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->product_type === 'bundle';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('option_name')
                    ->label('Option Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Drink Size'),

                TextInput::make('max_quantity')
                    ->label('Max Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),

                Toggle::make('is_default')
                    ->label('Is Default')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('option_name')
            ->columns([
                TextColumn::make('option_name')
                    ->label('Option Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('max_quantity')
                    ->label('Max Qty')
                    ->sortable(),

                TextColumn::make('is_default')
                    ->label('Default')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),

                TextColumn::make('optionItems_count')
                    ->counts('optionItems')
                    ->label('Items'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            OptionItemsRelationManager::class,
        ];
    }
}
