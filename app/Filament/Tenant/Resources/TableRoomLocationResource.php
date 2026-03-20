<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\TableRoomLocation;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use App\Enums\TableRoomLocation\LocationType;
use App\Enums\TableRoomLocation\ServiceChargeType;
use App\Filament\Tenant\Resources\TableRoomLocationResource\Pages;

class TableRoomLocationResource extends Resource
{
    protected static ?string $model = TableRoomLocation::class;
    protected static ?string $navigationGroup = 'Store';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('service_charge_label')
                    ->label('Service Charge Label')
                    ->placeholder('Service Charge')
                    ->helperText('Defaults to "Service Charge" when left blank.'),

                Select::make('service_charge_type')
                    ->label('Service Charge Type')
                    ->options(ServiceChargeType::filamentOptions())
                    ->default(ServiceChargeType::AUTO->value)
                    ->required()
                    ->live(),

                TextInput::make('service_charge')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->step(0.01)
                    ->label('Service Charge (%)')
                    ->helperText('Applied automatically when type is Auto. For Manual, set the amount in cart.')
                    ->disabled(fn (Get $get) => $get('service_charge_type') === ServiceChargeType::MANUAL->value),

                    Select::make('location_type')
                        ->label('Location Type')
                        ->options(LocationType::filamentOptions()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Location Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('service_charge_label')
                    ->label('Charge Label')
                    ->formatStateUsing(fn ($state) => $state ?: 'Service Charge')
                    ->sortable(),

                TextColumn::make('service_charge')
                    ->label('Service Charge (%)')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('service_charge_type')
                    ->label('Charge Type')
                    ->sortable()
                    ->searchable(),
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
            'index'  => Pages\ListTableRoomLocations::route('/'),
            'create' => Pages\CreateTableRoomLocation::route('/create'),
            'edit'   => Pages\EditTableRoomLocation::route('/{record}/edit'),
            'view'   => Pages\ViewTableRoomLocation::route('/{record}/view'),
        ];
    }
}
