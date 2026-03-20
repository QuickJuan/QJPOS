<?php

namespace App\Filament\Tenant\Resources\EmployeeResource\RelationManagers;

use App\Models\CashAdvance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CashAdvancesRelationManager extends RelationManager
{
    protected static string $relationship = 'cashAdvances';

    protected static ?string $title = 'Cash Advances';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')
                ->label('Amount')
                ->numeric()
                ->prefix('₱')
                ->required()
                ->minValue(1),

            Forms\Components\TextInput::make('terms')
                ->label('Terms (# of deductions)')
                ->numeric()
                ->required()
                ->minValue(1)
                ->default(1),

            Forms\Components\DatePicker::make('start_deduction_date')
                ->label('Start Deduction Date'),

            Forms\Components\Select::make('status')
                ->options([
                    'pending'   => 'Pending',
                    'approved'  => 'Approved',
                    'rejected'  => 'Rejected',
                    'completed' => 'Completed',
                ])
                ->default('pending')
                ->required(),

            Forms\Components\Textarea::make('reason')
                ->label('Reason')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('terms')
                    ->label('Terms')
                    ->suffix(' mo.'),

                Tables\Columns\TextColumn::make('amount_per_term')
                    ->label('Per Term')
                    ->money('PHP'),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Balance')
                    ->getStateUsing(function ($record) {
                        $totalPaid = $record->schedules->sum(fn ($s) => (float) $s->paid_amount);
                        return max(0, (float) $record->amount - $totalPaid);
                    })
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2))
                    ->color(fn ($record) => $record->status === 'completed' ? 'success' : 'warning'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'info'    => 'completed',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Filed')
                    ->date('M d, Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Cash Advance'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.tenant.resources.cash-advances.view', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
