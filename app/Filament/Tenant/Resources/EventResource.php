<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Events & Reservations';
    protected static ?string $navigationLabel = 'Events & Reservations';
    protected static ?int    $navigationSort  = 5;

    // ── Shared form schema ────────────────────────────────────────────────
    public static function formSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->options([
                        'reservation' => 'Reservation',
                        'meeting'     => 'Meeting',
                        'reminder'    => 'Reminder',
                        'holiday'     => 'Holiday',
                    ])
                    ->default('reservation')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'upcoming'  => 'Upcoming',
                        'ongoing'   => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('upcoming')
                    ->required(),

                Forms\Components\DateTimePicker::make('start_at')
                    ->label('Start')
                    ->required()
                    ->seconds(false),

                Forms\Components\DateTimePicker::make('end_at')
                    ->label('End')
                    ->required()
                    ->seconds(false)
                    ->after('start_at'),

                Forms\Components\Select::make('user_id')
                    ->label('Assigned To')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
        ];
    }

    // ── Filament form ─────────────────────────────────────────────────────
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema(static::formSchema());
    }

    // ── Table ─────────────────────────────────────────────────────────────
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reservation' => 'warning',
                        'meeting'     => 'info',
                        'reminder'    => 'primary',
                        'holiday'     => 'success',
                        default       => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming'  => 'info',
                        'ongoing'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('start_at')->label('Start')->dateTime('M j, Y g:i A')->sortable(),
                TextColumn::make('end_at')->label('End')->dateTime('M j, Y g:i A')->sortable(),
                TextColumn::make('user.name')->label('Assigned To')->default('—'),
            ])
            ->defaultSort('start_at')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'reservation' => 'Reservation',
                        'meeting'     => 'Meeting',
                        'reminder'    => 'Reminder',
                        'holiday'     => 'Holiday',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'upcoming'  => 'Upcoming',
                        'ongoing'   => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
