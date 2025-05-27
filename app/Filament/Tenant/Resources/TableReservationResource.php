<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\TableReservationResource\Pages;
use App\Filament\Tenant\Resources\TableReservationResource\RelationManagers;
use App\Models\TableReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TableReservationResource extends Resource
{
    protected static ?string $model = TableReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListTableReservations::route('/'),
            'create' => Pages\CreateTableReservation::route('/create'),
            'edit' => Pages\EditTableReservation::route('/{record}/edit'),
        ];
    }
}
