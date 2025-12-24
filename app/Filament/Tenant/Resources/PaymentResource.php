<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'invoice_no')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Order')
                            ->getOptionLabelFromRecordUsing(fn ($record) => sprintf('#%06d', $record->invoice_no ?? $record->id)),

                        Forms\Components\Select::make('payment_method_id')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Payment Method'),

                        Forms\Components\Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Currency'),

                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('₱')
                            ->label('Amount (Default Currency)'),

                        Forms\Components\TextInput::make('amount_in_payment_currency')
                            ->numeric()
                            ->helperText('Amount tendered by the customer in the selected currency')
                            ->label('Amount in Payment Currency'),

                        Forms\Components\TextInput::make('exchange_rate')
                            ->numeric()
                            ->step(.0001)
                            ->label('Exchange Rate'),

                        Forms\Components\TextInput::make('change_amount')
                            ->numeric()
                            ->prefix('₱')
                            ->label('Change (Default Currency)'),

                        Forms\Components\TextInput::make('reference_number')
                            ->maxLength(255)
                            ->label('Reference #'),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->label('Notes'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.invoice_no')
                    ->label('Invoice #')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('#%06d', $state) : '—')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('currency.code')
                    ->label('Currency')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->label('Amount')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_in_payment_currency')
                    ->label('Tendered (Currency)')
                    ->formatStateUsing(function ($state, Payment $record) {
                        if ($state === null) {
                            return '—';
                        }

                        $symbol = $record->currency?->symbol ?? '';
                        return sprintf('%s%s', $symbol, number_format((float) $state, 2));
                    }),

                Tables\Columns\TextColumn::make('change_amount')
                    ->money('PHP')
                    ->label('Change')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->label('Received At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->label('Method'),
                Tables\Filters\SelectFilter::make('currency_id')
                    ->relationship('currency', 'code')
                    ->label('Currency'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
