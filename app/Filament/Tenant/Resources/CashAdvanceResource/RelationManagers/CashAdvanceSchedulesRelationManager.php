<?php

namespace App\Filament\Tenant\Resources\CashAdvanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CashAdvanceSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Deduction Schedule';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('term_number')
                ->label('Term #')
                ->numeric()
                ->required(),
            Forms\Components\DatePicker::make('due_date')
                ->label('Due Date')
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->label('Scheduled Amount')
                ->numeric()
                ->prefix('₱')
                ->required(),
            Forms\Components\TextInput::make('paid_amount')
                ->label('Paid Amount')
                ->numeric()
                ->prefix('₱')
                ->default(0),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'partial' => 'Partial',
                    'paid'    => 'Paid',
                    'waived'  => 'Waived',
                ])
                ->required()
                ->default('pending'),
            Forms\Components\Textarea::make('notes')
                ->columnSpanFull()
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('term_number')
            ->defaultSort('term_number')
            ->columns([
                Tables\Columns\TextColumn::make('term_number')
                    ->label('Term')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount Due')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('PHP'),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->getStateUsing(fn ($record) => max(0, (float) $record->amount - (float) $record->paid_amount))
                    ->formatStateUsing(fn ($state) => '₱' . number_format($state, 2)),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'partial',
                        'success' => 'paid',
                        'gray'    => 'waived',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark term as fully paid?')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'partial']))
                    ->action(function ($record) {
                        $record->update([
                            'paid_amount' => $record->amount,
                            'status'      => 'paid',
                        ]);

                        // Auto-complete parent cash advance if all terms are paid
                        $cashAdvance = $record->cashAdvance;
                        if ($cashAdvance && $cashAdvance->schedules()->where('status', '!=', 'paid')->where('status', '!=', 'waived')->doesntExist()) {
                            $cashAdvance->update(['status' => 'completed']);
                        }

                        Notification::make()->title('Term marked as paid')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_partial')
                    ->label('Partial Payment')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'partial']))
                    ->form([
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $totalPaid = (float) $record->paid_amount + (float) $data['paid_amount'];
                        $status    = $totalPaid >= (float) $record->amount ? 'paid' : 'partial';
                        $record->update([
                            'paid_amount' => min($totalPaid, (float) $record->amount),
                            'status'      => $status,
                            'notes'       => $data['notes'] ?? $record->notes,
                        ]);
                        Notification::make()->title('Payment recorded')->success()->send();
                    }),

                Tables\Actions\Action::make('waive')
                    ->label('Waive')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'partial']))
                    ->action(function ($record) {
                        $record->update(['status' => 'waived']);
                        Notification::make()->title('Term waived')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
