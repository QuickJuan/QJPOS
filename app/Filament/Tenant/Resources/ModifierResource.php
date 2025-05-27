<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use App\Models\Modifier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\ModifierResource\Pages;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        
            ->schema([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->unique(ignoreRecord: true), // Only enforce uniqueness for new records, not when updating the same record

                TextInput::make('name')
                    ->required()
                    ->rules('required'),

                TagsInput::make('list')
                    ->placeholder('Enter modifier options, press tab or comma to separate')
                    ->separator(',')
                    ->splitKeys(['Tab', ',']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('list')
                    ->searchable()
                    ->sortable()
                    // ->formatStateUsing(fn ($state) => implode(', ', $state)),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListModifiers::route('/'),
            'create' => Pages\CreateModifier::route('/create'),
            'edit'   => Pages\EditModifier::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 8; // Second in group
    }
}
