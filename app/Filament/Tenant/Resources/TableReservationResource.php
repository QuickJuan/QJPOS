<?php
namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TableReservation;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Enums\TableReservationStatusType;
use Filament\Tables\Columns\SelectColumn;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Tenant\Resources\TableReservationResource\Pages;

class TableReservationResource extends Resource
{
    protected static ?string $model = TableReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('table_room_id')
                    ->label('Table Room')
                    ->relationship('tableRoom', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

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

    public static function table(Table $table): Table
    {
        return $table
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
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('reservation_to')
                    ->label('Reservation To')
                    ->dateTime()
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
            'index'  => Pages\ListTableReservations::route('/'),
            'create' => Pages\CreateTableReservation::route('/create'),
            'edit'   => Pages\EditTableReservation::route('/{record}/edit'),
            'view'   => Pages\ViewTableReservation::route('/{record}/view'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Events & Reservations';
    }

    public static function getNavigationSort(): ?int
    {
        return 2; // Second in group
    }
}
