<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    protected static ?string $navigationGroup = "Order";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('cashier_session_id')
                    ->label('Cashier Session')
                    ->relationship('cashierSession', 'beginning_cash')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('table_room_id')
                    ->label('Table Room')
                    ->relationship('tableRoom', 'name')
                    ->nullable()
                    ->preload()
                    ->searchable(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'settled' => 'Settled',
                        'refund' => 'Refund',
                        'credit' => 'Credit',
                    ])
                    ->default('settled')
                    ->required(),

                KeyValue::make('meta_data')
                    ->label('Meta Data')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cashierSession.beginning_cash')
                    ->label('Cashier Session')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tableRoom.name')
                    ->label('Table Room')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('id')
                    ->label('Receipt #')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'settled' => 'success',
                        'refund' => 'danger',
                        'credit' => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('tableRoom.customer_name')
                    ->label('Customer Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'settled' => 'Settled',
                        'refund' => 'Refund',
                        'credit' => 'Credit',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_receipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => route('receipt', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Refund Transaction')
                    ->modalDescription('Are you sure you want to refund this transaction? This action requires authorization.')
                    ->form([
                        Textarea::make('notes')
                            ->label('Refund Notes')
                            ->required()
                            ->placeholder('Enter reason for refund...'),
                        TextInput::make('supervisor_name')
                            ->label('Supervisor/Manager Name')
                            ->required()
                            ->placeholder('Enter supervisor name for authorization'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'status' => 'refund',
                            'meta_data' => array_merge($record->meta_data ?? [], [
                                'refund' => [
                                    'requested_by' => Auth::user()->name,
                                    'supervisor' => $data['supervisor_name'],
                                    'notes' => $data['notes'],
                                    'refunded_at' => now(),
                                ]
                            ])
                        ]);
                    })
                    ->visible(fn (Order $record): bool => $record->status === 'settled'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
