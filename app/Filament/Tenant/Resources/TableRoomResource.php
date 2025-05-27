<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\TableRoomResource\Pages;
use App\Filament\Tenant\Resources\TableRoomResource\RelationManagers;
use App\Models\TableRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TableRoomResource extends Resource
{
    protected static ?string $model = TableRoom::class;

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
            'index' => Pages\ListTableRooms::route('/'),
            'create' => Pages\CreateTableRoom::route('/create'),
            'edit' => Pages\EditTableRoom::route('/{record}/edit'),
        ];
    }
}
