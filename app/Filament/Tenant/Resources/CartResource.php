<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CartResource\Pages;
use App\Filament\Tenant\Resources\CartResource\RelationManagers\CartItemsRelationManager;
use App\Models\Cart;
use App\Services\CartService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model           = Cart::class;
    protected static ?string $navigationGroup = 'Order';
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';

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

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable(),

                Placeholder::make('guest_order_data')
                    ->label('Guest Order Data (read-only)')
                    ->columnSpanFull()
                    ->content(function ($record): string {
                        if (! $record) {
                            return 'N/A';
                        }
                        $checkout = data_get($record->meta_data, 'guest_checkout');
                        return $checkout
                            ? json_encode($checkout, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : 'No guest checkout data.';
                    })
                    ->visible(fn ($record): bool => filled(data_get($record?->meta_data, 'guest_checkout'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Online customer'),

                TextColumn::make('source')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => $state === 'customer' ? 'warning' : 'gray'),

                TextColumn::make('reference_no')
                    ->label('Reference #')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('meta_data.guest_checkout.name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('meta_data.guest_checkout.status')
                    ->label('Order Status')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'preparing' => 'warning',
                        'ready'     => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending'   => 'Pending',
                        'confirmed' => 'Confirmed',
                        'preparing' => 'Preparing',
                        'ready'     => 'Ready',
                        default     => '—',
                    }),

                TextColumn::make('cashierSession.beginning_cash')
                    ->label('Cashier Session')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Not assigned'),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Confirm: pending → confirmed
                Tables\Actions\Action::make('confirm_order')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Cart $record): bool =>
                        filled(data_get($record->meta_data, 'guest_checkout')) &&
                        data_get($record->meta_data, 'guest_checkout.status', 'pending') === 'pending'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Confirm this order?')
                    ->modalDescription('This lets the customer know their order has been received and is being processed.')
                    ->action(function (Cart $record) {
                        $meta = $record->meta_data ?? [];
                        data_set($meta, 'guest_checkout.status', 'confirmed');
                        $record->update(['meta_data' => $meta]);

                        Notification::make()
                            ->title('Order confirmed')
                            ->success()
                            ->send();
                    }),

                // Send to Kitchen: pending or confirmed → preparing
                Tables\Actions\Action::make('process_online_order')
                    ->label('Send To Kitchen')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Cart $record): bool =>
                        $record->source === 'customer' &&
                        blank($record->processed_at) &&
                        in_array(
                            data_get($record->meta_data, 'guest_checkout.status', 'pending'),
                            ['pending', 'confirmed']
                        )
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Send order to kitchen?')
                    ->modalDescription('This will send the items to the kitchen screen and mark the order as Preparing.')
                    ->action(function (Cart $record) {
                        app(CartService::class)->placeOrder([
                            'cart_id'        => $record->id,
                            'table_id'       => null,
                            'served_by'      => auth()->id(),
                            'serving_number' => data_get($record->meta_data, 'guest_checkout.reference_no'),
                        ]);

                        $meta = $record->meta_data ?? [];
                        data_set($meta, 'guest_checkout.status', 'preparing');
                        $record->update(['meta_data' => $meta]);

                        Notification::make()
                            ->title('Order sent to kitchen — status set to Preparing')
                            ->success()
                            ->send();
                    }),

                // Mark Ready: preparing → ready
                Tables\Actions\Action::make('mark_ready')
                    ->label('Mark Ready')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Cart $record): bool =>
                        data_get($record->meta_data, 'guest_checkout.status') === 'preparing'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark order as ready?')
                    ->modalDescription('The customer will see their order status change to Ready for pickup/delivery.')
                    ->action(function (Cart $record) {
                        $meta = $record->meta_data ?? [];
                        data_set($meta, 'guest_checkout.status', 'ready');
                        $record->update(['meta_data' => $meta]);

                        Notification::make()
                            ->title('Order marked as ready')
                            ->success()
                            ->send();
                    }),

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
            CartItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit'   => Pages\EditCart::route('/{record}/edit'),
            'view'   => Pages\ViewCart::route('/{record}/view'),
        ];
    }
}
