<?php

namespace App\Filament\Tenant\Resources\ExpenseResource\RelationManagers;

use App\Models\ExpensePayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title       = 'Payment Schedule';

    public function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('due_date')
                ->label('Due Date')
                ->required()
                ->native(false)
                ->default(today()),

            TextInput::make('amount')
                ->label('Amount')
                ->required()
                ->numeric()
                ->prefix('₱')
                ->minValue(0.01)
                ->step(0.01),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->recordTitleAttribute('due_date')
            ->columns([
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),

                IconColumn::make('is_paid')
                    ->label('Paid?')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('paid_date')
                    ->label('Date Paid')
                    ->date('M d, Y')
                    ->placeholder('—'),

                TextColumn::make('reference_number')
                    ->label('Reference #')
                    ->placeholder('—'),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('paidBy.name')
                    ->label('Paid By')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Installment')
                    ->modalHeading('Add Payment Installment')
                    ->after(fn ($record) => $record->expense->updateStatus()),
            ])
            ->actions([
                // Mark as Paid action
                Action::make('markPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ExpensePayment $record) => ! $record->is_paid)
                    ->form([
                        DatePicker::make('paid_date')
                            ->label('Date Paid')
                            ->required()
                            ->native(false)
                            ->default(today()),

                        TextInput::make('reference_number')
                            ->label('Reference / OR Number')
                            ->maxLength(100)
                            ->placeholder('Transaction or receipt reference'),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->modalHeading('Mark Installment as Paid')
                    ->modalSubmitActionLabel('Confirm Payment')
                    ->action(function (ExpensePayment $record, array $data): void {
                        $record->update([
                            'is_paid'          => true,
                            'paid_date'        => $data['paid_date'],
                            'reference_number' => $data['reference_number'] ?? null,
                            'remarks'          => $data['remarks'] ?? $record->remarks,
                            'paid_by'          => auth()->id(),
                        ]);

                        $record->expense->updateStatus();

                        Notification::make()
                            ->title('Payment recorded')
                            ->body('Installment has been marked as paid.')
                            ->success()
                            ->send();
                    }),

                // Unmark as Paid action
                Action::make('unmarkPaid')
                    ->label('Undo Payment')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->visible(fn (ExpensePayment $record) => $record->is_paid)
                    ->requiresConfirmation()
                    ->modalHeading('Undo Payment')
                    ->modalDescription('This will remove the payment record for this installment. Continue?')
                    ->action(function (ExpensePayment $record): void {
                        $record->update([
                            'is_paid'          => false,
                            'paid_date'        => null,
                            'reference_number' => null,
                            'paid_by'          => null,
                        ]);

                        $record->expense->updateStatus();

                        Notification::make()
                            ->title('Payment reversed')
                            ->body('Installment has been marked as unpaid.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->after(fn ($record) => $record->expense->updateStatus()),

                Tables\Actions\DeleteAction::make()
                    ->after(fn ($record) => $record->expense->updateStatus()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
