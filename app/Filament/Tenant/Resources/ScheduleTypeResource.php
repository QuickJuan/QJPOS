<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ScheduleTypeResource\Pages;
use App\Models\ScheduleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduleTypeResource extends Resource
{
    protected static ?string $model = ScheduleType::class;

    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Human Resource';
    protected static ?string $navigationLabel = 'Shift Types';
    protected static ?int    $navigationSort  = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Shift Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Shift Name')
                        ->placeholder('e.g. Opening, Graveyard, Mid-shift')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\TimePicker::make('expected_time_in')
                        ->label('Expected Time-In')
                        ->required()
                        ->seconds(false)
                        ->helperText('The standard clock-in time for this shift'),

                    Forms\Components\TextInput::make('duration_hours')
                        ->label('Duration (hours)')
                        ->numeric()
                        ->default(8)
                        ->minValue(1)
                        ->maxValue(24)
                        ->required()
                        ->suffix('hrs')
                        ->helperText('Workers typically render 8 hours'),

                    Forms\Components\ColorPicker::make('color')
                        ->label('Badge Color')
                        ->default('#6366f1')
                        ->helperText('Color used in schedules and badges'),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Optional notes about this shift type')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('')
                    ->width('40px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Shift Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('expected_time_in')
                    ->label('Expected Time-In')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::createFromTimeString($state)->format('g:i A'))
                    ->icon('heroicon-m-clock'),

                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Duration')
                    ->suffix(' hrs')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('work_schedules_count')
                    ->label('Assigned')
                    ->counts('workSchedules')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListScheduleTypes::route('/'),
            'create' => Pages\CreateScheduleType::route('/create'),
            'edit'   => Pages\EditScheduleType::route('/{record}/edit'),
        ];
    }
}
