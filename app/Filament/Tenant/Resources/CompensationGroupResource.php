<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CompensationGroupResource\Pages;
use App\Models\CompensationGroup;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompensationGroupResource extends Resource
{
    protected static ?string $model = CompensationGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'HR and Payroll';

    protected static ?int $navigationSort = 30;

    protected static ?string $label = 'Compensation Group';

    protected static ?string $pluralLabel = 'Compensation Groups';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Group Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Government Mandated, De Minimis Benefits'),

                Select::make('applies_to')
                    ->label('Applies To')
                    ->options([
                        'income'    => 'Income / Earnings',
                        'deduction' => 'Deduction',
                        'both'      => 'Both',
                    ])
                    ->default('both')
                    ->required(),

                ColorPicker::make('color')
                    ->label('Color')
                    ->default('#6366f1'),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0),

                Textarea::make('description')
                    ->label('Description')
                    ->nullable()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('')
                    ->width('40px'),

                TextColumn::make('name')
                    ->label('Group Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applies_to')
                    ->label('Applies To')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'income'    => 'success',
                        'deduction' => 'danger',
                        'both'      => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'income'    => 'Income',
                        'deduction' => 'Deduction',
                        'both'      => 'Both',
                        default     => $state,
                    }),

                TextColumn::make('compensation_types_count')
                    ->label('Types')
                    ->counts('compensationTypes')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->placeholder('–'),
            ])
            ->defaultSort('sort_order')
            ->filters([])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCompensationGroups::route('/'),
            'create' => Pages\CreateCompensationGroup::route('/create'),
            'edit'   => Pages\EditCompensationGroup::route('/{record}/edit'),
        ];
    }
}
