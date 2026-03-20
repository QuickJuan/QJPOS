<?php

namespace App\Filament\Tenant\Resources\EmployeeResource\RelationManagers;

use App\Models\CompensationType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeCompensationsRelationManager extends RelationManager
{
    protected static string $relationship = 'compensations';

    protected static ?string $label = 'Compensation / Benefit / Deduction';

    protected static ?string $pluralLabel = 'Compensations, Benefits & Deductions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('compensation_type_id')
                    ->label('Compensation / Deduction Type')
                    ->options(function () {
                        return CompensationType::query()
                            ->with('group')
                            ->where('is_active', true)
                            ->orderBy('compensation_group_id')
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function (CompensationType $type) {
                                $groupName = $type->group?->name ?? 'Ungrouped';
                                return [$type->id => "[{$groupName}] {$type->name}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Select from the configured compensation and deduction types'),

                TextInput::make('amount')
                    ->label('Override Amount (₱)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->nullable()
                    ->prefix('₱')
                    ->helperText('Leave blank to use the default amount from the compensation type'),

                TextInput::make('rate')
                    ->label('Override Rate (%)')
                    ->numeric()
                    ->step(0.0001)
                    ->minValue(0)
                    ->maxValue(100)
                    ->nullable()
                    ->suffix('%')
                    ->helperText('Leave blank to use the default rate'),

                DatePicker::make('effective_date')
                    ->label('Effective Date')
                    ->nullable(),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->nullable()
                    ->helperText('Leave blank if this compensation is ongoing'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable()
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('compensationType.group.name')
                    ->label('Group')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('compensationType.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('compensationType.type')
                    ->label('Direction')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'income'    => 'success',
                        'deduction' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'income'    => 'Income',
                        'deduction' => 'Deduction',
                        default     => $state,
                    }),

                TextColumn::make('effective_amount')
                    ->label('Effective Amount (₱)')
                    ->getStateUsing(fn ($record) => $record->effective_amount)
                    ->money('PHP')
                    ->placeholder('–'),

                TextColumn::make('effective_rate')
                    ->label('Effective Rate (%)')
                    ->getStateUsing(fn ($record) => $record->effective_rate)
                    ->suffix('%')
                    ->placeholder('–'),

                TextColumn::make('effective_date')
                    ->label('Effective Date')
                    ->date()
                    ->placeholder('Always'),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->placeholder('Ongoing'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
