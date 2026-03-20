<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\EmployeeResource\Pages;
use App\Filament\Tenant\Resources\EmployeeResource\RelationManagers\CashAdvancesRelationManager;
use App\Filament\Tenant\Resources\EmployeeResource\RelationManagers\EmployeeCompensationsRelationManager;
use App\Filament\Tenant\Resources\EmployeeResource\RelationManagers\LeaveCreditsRelationManager;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'HR and Payroll';

    protected static ?int $navigationSort = 29;

    protected static ?string $label = 'Employee';

    protected static ?string $pluralLabel = 'Employees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Employee Details')
                    ->tabs([
                        Tabs\Tab::make('Personal & Employment')
                            ->schema([
                                Select::make('user_id')
                                    ->label('User Account')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Link this employee record to a system user account'),

                                TextInput::make('employee_no')
                                    ->label('Employee Number')
                                    ->nullable()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder('e.g. EMP-0001'),

                                TextInput::make('position')
                                    ->label('Position / Designation')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Cashier, Cook, Manager'),

                                TextInput::make('department')
                                    ->label('Department')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Operations, Kitchen, Admin'),

                                Select::make('branch_id')
                                    ->label('Branch')
                                    ->relationship('branch', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),

                                Select::make('employment_type')
                                    ->label('Employment Type')
                                    ->options(Employee::employmentTypeOptions())
                                    ->default('regular')
                                    ->required(),

                                Select::make('status')
                                    ->label('Employment Status')
                                    ->options([
                                        'active'    => 'Active',
                                        'inactive'  => 'Inactive',
                                        'separated' => 'Separated',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Compensation')
                            ->schema([
                                DatePicker::make('date_hired')
                                    ->label('Date Hired')
                                    ->nullable(),

                                DatePicker::make('date_regularized')
                                    ->label('Date Regularized')
                                    ->nullable(),

                                DatePicker::make('date_separated')
                                    ->label('Date Separated')
                                    ->nullable(),

                                Select::make('pay_frequency')
                                    ->label('Pay Frequency')
                                    ->options([
                                        'monthly'     => 'Monthly',
                                        'semi_monthly' => 'Semi-Monthly (15th & End of Month)',
                                        'weekly'      => 'Weekly',
                                        'bi_weekly'   => 'Bi-Weekly (Every 2 Weeks)',
                                    ])
                                    ->default('semi_monthly')
                                    ->required(),

                                TextInput::make('basic_salary')
                                    ->label('Basic Monthly Salary (₱)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('₱'),

                                TextInput::make('daily_rate')
                                    ->label('Daily Rate (₱)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('₱')
                                    ->helperText('Leave 0 to compute from basic salary ÷ 26 days'),

                                TextInput::make('hourly_rate')
                                    ->label('Hourly Rate (₱)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('₱')
                                    ->helperText('Leave 0 to compute from daily rate ÷ 8 hours'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Government IDs & Tax')
                            ->schema([
                                Select::make('tax_status')
                                    ->label('BIR Tax Status')
                                    ->options(Employee::taxStatusOptions())
                                    ->default('S')
                                    ->required()
                                    ->helperText('Civil status code used for BIR withholding tax computation')
                                    ->columnSpanFull(),

                                TextInput::make('sss_no')
                                    ->label('SSS Number')
                                    ->nullable()
                                    ->maxLength(20)
                                    ->placeholder('xx-xxxxxxx-x'),

                                TextInput::make('philhealth_no')
                                    ->label('PhilHealth / PHIC Number')
                                    ->nullable()
                                    ->maxLength(20)
                                    ->placeholder('xx-xxxxxxxxx-x'),

                                TextInput::make('pagibig_no')
                                    ->label('Pag-IBIG / HDMF MID Number')
                                    ->nullable()
                                    ->maxLength(20)
                                    ->placeholder('xxxx-xxxx-xxxx'),

                                TextInput::make('tin_no')
                                    ->label('TIN Number')
                                    ->nullable()
                                    ->maxLength(20)
                                    ->placeholder('xxx-xxx-xxx-xxx'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Banking')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->nullable()
                                    ->maxLength(255)
                                    ->placeholder('e.g. BDO, BPI, Metrobank, GCash'),

                                TextInput::make('bank_account_no')
                                    ->label('Bank Account / E-Wallet Number')
                                    ->nullable()
                                    ->maxLength(100)
                                    ->placeholder('Account or mobile number'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Notes')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->nullable()
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_no')
                    ->label('Employee No.')
                    ->searchable()
                    ->sortable()
                    ->placeholder('–'),

                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->placeholder('–'),

                TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->placeholder('–'),

                TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'regular'       => 'success',
                        'probationary'  => 'warning',
                        'part_time'     => 'info',
                        'contractual'   => 'primary',
                        'casual'        => 'gray',
                        'seasonal'      => 'gray',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => Employee::employmentTypeOptions()[$state] ?? $state),

                TextColumn::make('pay_frequency')
                    ->label('Pay Freq.')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'monthly'      => 'Monthly',
                        'semi_monthly' => 'Semi-Monthly',
                        'weekly'       => 'Weekly',
                        'bi_weekly'    => 'Bi-Weekly',
                        default        => $state,
                    })
                    ->toggleable(),

                TextColumn::make('basic_salary')
                    ->label('Basic Salary')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'    => 'success',
                        'inactive'  => 'warning',
                        'separated' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('date_hired')
                    ->label('Date Hired')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('status')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                        'separated' => 'Separated',
                    ]),

                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options(Employee::employmentTypeOptions()),

                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name'),
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
            EmployeeCompensationsRelationManager::class,
            CashAdvancesRelationManager::class,
            LeaveCreditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view'   => Pages\ViewEmployee::route('/{record}/view'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
