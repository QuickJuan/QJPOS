<?php

namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\TableRoom;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\TableRoomStatusType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Tenant\Resources\TableRoomResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Tenant\Resources\TableRoomResource\RelationManagers;
use App\Filament\Tenant\Resources\TableRoomResource\RelationManagers\TableReservationsRelationManager;

class TableRoomResource extends Resource
{
    protected static ?string $model = TableRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->label('Table/Room Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('chairs')
                            ->label('Number of Chairs')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        Forms\Components\Select::make('merge_to')
                            ->label('Merge To')
                            ->helperText('Select another table/room to merge with this one. This will allow combining the seating capacity.')
                            ->options(function ($get, $record) {
                                $currentId = $record?->id;
                                return \App\Models\TableRoom::query()
                                    ->when($currentId, fn($q) => $q->where('id', '!=', $currentId))
                                    ->pluck('name', 'id');
                            })
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(TableRoomStatusType::filamentOptions())
                            ->default(TableRoomStatusType::VACANT->value)
                            ->required(),

                        Forms\Components\Select::make('with_timeframe')
                            ->label('Time monitoring')
                            ->helperText('Enable this if you want to set specific time in and time out for this table/room.')
                            ->options([
                                1 => 'With Timeframe',
                                0 => 'Without Timeframe',
                            ])
                            ->default(0)
                            ->required(),

                      
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Table Properties')
                    ->schema([
                        
                        Forms\Components\TextInput::make('table_width')
                            ->label('Table Width')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),

                        Forms\Components\TextInput::make('table_height')
                            ->label('Table Height')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),

                        Forms\Components\TextInput::make('table_x')
                            ->label('Table X Coordinate')
                            ->default(0),

                        Forms\Components\TextInput::make('table_y')
                            ->label('Table Y Coordinate')
                            ->default(0),

                        SpatieMediaLibraryFileUpload::make('featured_image')
                            ->label('Table/Room Image')
                            ->collection('featured_image')
                            ->image()
                            ->maxSize(1024) // 1MB
                            ->acceptedFileTypes(['image/*']),


                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Room Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('chairs')
                    ->label('Chairs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Table/Room deleted successfully')
                    ->color('danger'),
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
            TableReservationsRelationManager::class,
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
