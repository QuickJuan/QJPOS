<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PreparationLocation;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\PreparationLocationResource\Pages;

class PreparationLocationResource extends Resource
{
    protected static ?string $model = PreparationLocation::class;

    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Products';
    protected static ?int    $navigationSort  = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('description')
                    ->required()
                    ->maxLength(255),

                Toggle::make('printable')
                    ->label('Printable')
                    ->default(true),

                Toggle::make('show_on_screen')
                    ->label('Show on Screen')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Description')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('printable')
                    ->boolean()
                    ->label('Printable'),

                IconColumn::make('show_on_screen')
                    ->boolean()
                    ->label('Show on Screen'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
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
            'index'  => Pages\ListPreparationLocations::route('/'),
            'create' => Pages\CreatePreparationLocation::route('/create'),
            'edit'   => Pages\EditPreparationLocation::route('/{record}/edit'),
        ];
    }
}
