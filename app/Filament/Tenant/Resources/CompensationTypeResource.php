<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CompensationTypeResource\Pages;
use App\Models\CompensationType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompensationTypeResource extends Resource
{
    protected static ?string $model = CompensationType::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'HR and Payroll';

    protected static ?int $navigationSort = 31;

    protected static ?string $label = 'Compensation Type';

    protected static ?string $pluralLabel = 'Compensation Types';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('compensation_group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('Short identifier, e.g. SSS, PHIC, PAGIBIG, RICE_ALLOW')
                    ->placeholder('e.g. SSS'),

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. SSS Contribution'),

                Select::make('type')
                    ->label('Direction')
                    ->options([
                        'income'    => 'Income / Earning',
                        'deduction' => 'Deduction',
                    ])
                    ->required()
                    ->default('deduction'),

                Select::make('computation_type')
                    ->label('Computation Type')
                    ->options([
                        'fixed'      => 'Fixed Amount',
                        'percentage' => 'Percentage of Salary',
                        'table'      => 'Manual / Govt Table',
                    ])
                    ->default('fixed')
                    ->required()
                    ->live(),

                TextInput::make('default_amount')
                    ->label('Default Amount (₱)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->nullable()
                    ->visible(fn (Get $get) => in_array($get('computation_type'), ['fixed', 'table']))
                    ->helperText('For "Manual / Govt Table" types: enter a common default (e.g. minimum bracket). The actual per-employee amount should be overridden per payroll period in the employee\'s Compensations tab after checking the government\'s contribution table.'),

                TextInput::make('default_rate')
                    ->label('Default Rate (%)')
                    ->numeric()
                    ->step(0.0001)
                    ->minValue(0)
                    ->maxValue(100)
                    ->nullable()
                    ->visible(fn (Get $get) => $get('computation_type') === 'percentage')
                    ->helperText('e.g. 2.5 for 2.5% of basic salary'),

                Toggle::make('is_taxable')
                    ->label('Taxable')
                    ->default(false)
                    ->helperText('Affects BIR withholding tax computation'),

                Toggle::make('is_mandatory')
                    ->label('Government Mandatory')
                    ->default(false)
                    ->helperText('e.g. SSS, PhilHealth, Pag-IBIG'),

                Toggle::make('is_employer_shared')
                    ->label('Employer Shares Contribution')
                    ->default(false)
                    ->helperText('Employer also contributes a portion (e.g. SSS, PhilHealth)'),

                Toggle::make('applies_to_regular')
                    ->label('Applies to Regular Employees')
                    ->default(true),

                Toggle::make('applies_to_part_time')
                    ->label('Applies to Part-Time Employees')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->minValue(0),

                Textarea::make('notes')
                    ->label('Notes / Regulatory Basis')
                    ->nullable()
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('e.g. R.A. 8282 – Social Security Act; max non-taxable ₱2,000/month'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group.name')
                    ->label('Group')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Direction')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'income'    => 'success',
                        'deduction' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                IconColumn::make('is_mandatory')
                    ->label('Mandatory')
                    ->boolean(),

                IconColumn::make('is_taxable')
                    ->label('Taxable')
                    ->boolean(),

                TextColumn::make('computation_type')
                    ->label('Computation')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'fixed'      => 'Fixed',
                        'percentage' => 'Percentage',
                        'table'      => 'Manual / Govt Table',
                        default      => $state,
                    }),

                TextColumn::make('default_amount')
                    ->label('Default Amount')
                    ->money('PHP')
                    ->placeholder('–'),

                TextColumn::make('default_rate')
                    ->label('Default Rate')
                    ->suffix('%')
                    ->placeholder('–'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->defaultSort('compensation_group_id')
            ->filters([
                SelectFilter::make('compensation_group_id')
                    ->label('Group')
                    ->relationship('group', 'name'),

                SelectFilter::make('type')
                    ->label('Direction')
                    ->options([
                        'income'    => 'Income',
                        'deduction' => 'Deduction',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCompensationTypes::route('/'),
            'create' => Pages\CreateCompensationType::route('/create'),
            'edit'   => Pages\EditCompensationType::route('/{record}/edit'),
        ];
    }
}
