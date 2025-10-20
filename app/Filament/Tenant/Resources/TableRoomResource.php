<?php
namespace App\Filament\Tenant\Resources;

use App\Enums\TableRoomStatusType;
use App\Filament\Tenant\Resources\TableRoomResource\Pages;
use App\Filament\Tenant\Resources\TableRoomResource\RelationManagers\TableReservationsRelationManager;
use App\Models\TableRoom;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TableRoomResource extends Resource
{
    protected static ?string $model = TableRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->columns(3)
                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('table_room_location_id')
                            ->label('Table Room Location')
                            ->relationship('tableRoomLocation', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('Table/Room Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('chairs')
                            ->label('Number of Chairs')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        Select::make('merge_to')
                            ->label('Merge To')
                            ->helperText('Select another table/room to merge with this one. This will allow combining the seating capacity.')
                            ->options(function ($get, $record) {
                                $currentId = $record?->id;
                                return TableRoom::query()
                                    ->when($currentId, fn($q) => $q->where('id', '!=', $currentId))
                                    ->pluck('name', 'id');
                            })
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Select::make('status')
                            ->label('Status')
                            ->options(TableRoomStatusType::filamentOptions())
                            ->default(TableRoomStatusType::VACANT->value)
                            ->required(),

                        Select::make('with_timeframe')
                            ->label('Time monitoring')
                            ->helperText('Enable this if you want to set specific time in and time out for this table/room.')
                            ->options([
                                1 => 'With Timeframe',
                                0 => 'Without Timeframe',
                            ])
                            ->default(0)
                            ->required(),

                    ]),

                Section::make('Table Properties')
                    ->schema([

                        TextInput::make('table_width')
                            ->label('Table Width')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),

                        TextInput::make('table_height')
                            ->label('Table Height')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1000),

                        TextInput::make('table_x')
                            ->label('Table X Coordinate')
                            ->default(0),

                        TextInput::make('table_y')
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
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tableRoomLocation.name')
                    ->label('Table Room Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Room Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chairs')
                    ->label('Chairs')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
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
            TableReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTableRooms::route('/'),
            'create' => Pages\CreateTableRoom::route('/create'),
            'edit'   => Pages\EditTableRoom::route('/{record}/edit'),
            'view'   => Pages\ViewTableRoom::route('/{record}/view'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Table / Rooms';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Second in group
    }
}
