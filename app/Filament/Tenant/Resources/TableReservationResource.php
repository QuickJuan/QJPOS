<?php

namespace App\Filament\Tenant\Resources;

use App\Enums\TableReservationStatusType;
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
                Forms\Components\Select::make('table_room_id')
                    ->label('Table Room')
                    ->relationship('tableRoom', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Section::make('Reservation Details')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\DateTimePicker::make('reservation_from')
                            ->label('Reservation From')
                            ->required(),

                        Forms\Components\DateTimePicker::make('reservation_to')
                            ->label('Reservation To')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(TableReservationStatusType::filamentOptions())
                            ->default(TableReservationStatusType::ACTIVE->value),
                    ]),

                Forms\Components\Section::make('Contact Information')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Customer Name')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('pax')
                            ->label('Number of Pax')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1),

                        Forms\Components\TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Contact Phone'),

                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(150)
                            ->label('Contact Email'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tableRoom.name')
                    ->label('Table Room')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservation_from')
                    ->label('Reservation From')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservation_to')
                    ->label('Reservation To')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->options(TableReservationStatusType::filamentOptions())
                    ->sortable(),

                Tables\Columns\TextColumn::make('pax')
                    ->label('Number of Pax'),

                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Contact Phone'),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Contact Email'),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->wrap(),
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

    public static function getNavigationGroup(): ?string
    {
        return 'Table / Rooms';
    }

    public static function getNavigationSort(): ?int
    {
        return 2; // Second in group
    }
}
