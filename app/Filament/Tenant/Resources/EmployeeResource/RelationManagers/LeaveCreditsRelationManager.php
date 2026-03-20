<?php

namespace App\Filament\Tenant\Resources\EmployeeResource\RelationManagers;

use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveCreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveCredits';

    protected static ?string $title = 'Leave Credits';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('leave_type_id')
                ->label('Leave Type')
                ->options(LeaveType::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('year')
                ->label('Year')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->minValue(2000)
                ->maxValue(2099),

            Forms\Components\TextInput::make('total_days')
                ->label('Allocated Days')
                ->numeric()
                ->required()
                ->minValue(0)
                ->step(0.5)
                ->suffix('days'),

            Forms\Components\TextInput::make('used_days')
                ->label('Used Days')
                ->numeric()
                ->required()
                ->default(0)
                ->minValue(0)
                ->step(0.5)
                ->suffix('days'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('leave_type_id')
            ->defaultSort('year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Allocated')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(' days'),

                Tables\Columns\TextColumn::make('used_days')
                    ->label('Used')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(' days')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Remaining')
                    ->getStateUsing(fn ($record) => max(0, (float) $record->total_days - (float) $record->used_days))
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . ' days')
                    ->color(fn ($record) => ((float) $record->total_days - (float) $record->used_days) > 0 ? 'success' : 'danger'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Allocate Credits'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
