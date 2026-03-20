<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'HR and Payroll';
    protected static ?int    $navigationSort  = 36;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->placeholder('e.g. SL, VL, ML'),

                    Forms\Components\TextInput::make('name')
                        ->label('Leave Type Name')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2)
                        ->placeholder('e.g. Sick Leave'),
                ]),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('default_days_per_year')
                        ->label('Default Days / Year')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(15)
                        ->helperText('Applied when allocating leave credits for new year.'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Toggle::make('is_paid')
                        ->label('Paid Leave')
                        ->helperText('Employee is paid during this leave.')
                        ->default(true),

                    Forms\Components\Toggle::make('requires_document')
                        ->label('Requires Supporting Document')
                        ->helperText('e.g. medical certificate for Sick Leave.')
                        ->default(false),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Toggle::make('applies_to_regular')
                        ->label('Applies to Regular Employees')
                        ->default(true),

                    Forms\Components\Toggle::make('applies_to_part_time')
                        ->label('Applies to Part-Time Employees')
                        ->default(false),
                ]),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes / Legal Basis')
                    ->placeholder('e.g. R.A. 11210 — 105-Day Expanded Maternity Leave Act')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('default_days_per_year')
                    ->label('Days / Year')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_document')
                    ->label('Doc Required')
                    ->boolean(),

                Tables\Columns\IconColumn::make('applies_to_regular')
                    ->label('Regular')
                    ->boolean(),

                Tables\Columns\IconColumn::make('applies_to_part_time')
                    ->label('Part-Time')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit'   => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}
