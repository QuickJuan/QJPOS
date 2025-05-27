<?php

namespace App\Filament\Tenant\Resources;

use App\Enums\TableRoomStatusType;
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
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('name')
                    ->label('Room Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('chairs')
                    ->label('Number of Chairs')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(100),

                Forms\Components\Toggle::make('with_timeframe')
                    ->label('With Timeframe')
                    ->default(false),

                Forms\Components\Select::make('merge_to')
                    ->label('Merge To')
                    ->relationship('mergeTo', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(TableRoomStatusType::filamentOptions())
                    ->default(TableRoomStatusType::VACANT->value)
                    ->required(),

                Forms\Components\Section::make('Time Management')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\DateTimePicker::make('time_in')
                            ->label('Time In')
                            ->required(),

                        Forms\Components\DateTimePicker::make('time_out')
                            ->label('Time Out'),

                        Forms\Components\TextInput::make('limit_hours')
                            ->label('Limit Hours')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(24)
                            ->default(0),
                    ]),

                Forms\Components\Section::make('Table Dimensions')
                    ->columnSpan(1)
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
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),

                        Forms\Components\TextInput::make('table_y')
                            ->label('Table Y Coordinate')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),
                    ]),
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

                Tables\Columns\TextColumn::make('time_in')
                    ->label('Time In')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_out')
                    ->label('Time Out')
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
            'index' => Pages\ListTableRooms::route('/'),
            'create' => Pages\CreateTableRoom::route('/create'),
            'edit' => Pages\EditTableRoom::route('/{record}/edit'),
        ];
    }
}
