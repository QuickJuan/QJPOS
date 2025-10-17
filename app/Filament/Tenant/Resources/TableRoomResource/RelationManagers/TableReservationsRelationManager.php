<?php
namespace App\Filament\Tenant\Resources\TableRoomResource\RelationManagers;

use App\Enums\TableReservationStatusType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TableReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'tableReservations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                Section::make('Reservation Details')
                    ->columnSpan(1)
                    ->schema([
                        DateTimePicker::make('reservation_from')
                            ->label('Reservation From')
                            ->required(),

                        DateTimePicker::make('reservation_to')
                            ->label('Reservation To')
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(TableReservationStatusType::filamentOptions())
                            ->default(TableReservationStatusType::ACTIVE->value),
                    ]),

                Section::make('Contact Information')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Customer Name')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('pax')
                            ->label('Number of Pax')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1),

                        TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Contact Phone'),

                        TextInput::make('contact_email')
                            ->email()
                            ->maxLength(150)
                            ->label('Contact Email'),

                        Textarea::make('notes')
                            ->label('Notes'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('tableRoom.name')
                    ->label('Table Room')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reservation_from')
                    ->label('Reservation From')
                    ->dateTime('F d, Y h:i A')
                    ->sortable(),

                TextColumn::make('reservation_to')
                    ->label('Reservation To')
                    ->dateTime('F d, Y h:i A')
                    ->sortable(),

                SelectColumn::make('status')
                    ->options(TableReservationStatusType::filamentOptions())
                    ->sortable(),

                TextColumn::make('pax')
                    ->label('Number of Pax'),

                TextColumn::make('contact_phone')
                    ->label('Contact Phone'),

                TextColumn::make('contact_email')
                    ->label('Contact Email'),

                TextColumn::make('notes')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
