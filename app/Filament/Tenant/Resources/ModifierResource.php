<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ModifierResource\Pages;
use App\Models\Modifier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required(),

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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListModifiers::route('/'),
            'create' => Pages\CreateModifier::route('/create'),
            'edit'   => Pages\EditModifier::route('/{record}/edit'),
            'view'   => Pages\ViewModifier::route('/{record}/view'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 9; // Second in group
    }
}
