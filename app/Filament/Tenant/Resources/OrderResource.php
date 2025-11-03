<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    protected static ?string $navigationGroup = "Order";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('cashier_session_id')
                    ->label('Cashier Session')
                    ->relationship('cashierSession', 'beginning_cash')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('table_room_id')
                    ->label('Table Room')
                    ->relationship('tableRoom', 'name')
                    ->nullable()
                    ->preload()
                    ->searchable(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable(),

                KeyValue::make('meta_data')
                    ->label('Meta Data')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cashierSession.beginning_cash')
                    ->label('Cashier Session')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tableRoom.name')
                    ->label('Table Room')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
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
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
