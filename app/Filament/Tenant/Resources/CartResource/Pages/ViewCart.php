<?php
namespace App\Filament\Tenant\Resources\CartResource\Pages;

use App\Filament\Tenant\Resources\CartResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_order_status')
                ->label('Update Order Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => filled(data_get($this->record->meta_data, 'guest_checkout')))
                ->form([
                    Select::make('status')
                        ->label('New Status')
                        ->options([
                            'pending'   => 'Pending',
                            'confirmed' => 'Confirmed',
                            'preparing' => 'Preparing',
                            'ready'     => 'Ready',
                        ])
                        ->default(fn () => data_get($this->record->meta_data, 'guest_checkout.status', 'pending'))
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $meta = $this->record->meta_data ?? [];
                    data_set($meta, 'guest_checkout.status', $data['status']);
                    $this->record->update(['meta_data' => $meta]);

                    Notification::make()
                        ->title('Order status updated to ' . ucfirst($data['status']))
                        ->success()
                        ->send();

                    $this->refreshFormData(['meta_data']);
                }),

            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.carts.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Cart Details')
                    ->schema([
                        TextEntry::make('cashier.name')
                            ->placeholder('Online customer'),

                        TextEntry::make('cashierSession.beginnning_cash')
                            ->label('Cashier Session Beginning Cash'),

                        TextEntry::make('source')
                            ->badge()
                            ->placeholder('staff'),

                        TextEntry::make('reference_no')
                            ->label('Reference #')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),

                Section::make('Online Customer Details')
                    ->schema([
                        TextEntry::make('meta_data.guest_checkout.name')
                            ->label('Customer Name')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.phone')
                            ->label('Phone Number')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.email')
                            ->label('Email')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.order_type')
                            ->label('Order Type')
                            ->placeholder('N/A')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'N/A'),

                        TextEntry::make('meta_data.guest_checkout.status')
                            ->label('Order Status')
                            ->badge()
                            ->placeholder('N/A')
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
                                default     => 'N/A',
                            }),

                        TextEntry::make('meta_data.guest_checkout.address')
                            ->label('Address')
                            ->columnSpanFull()
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.notes')
                            ->label('Guest Notes')
                            ->columnSpanFull()
                            ->placeholder('N/A'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record): bool => filled(data_get($record->meta_data, 'guest_checkout'))),

                Section::make('Cart Notes & Metadata')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.reference_no')
                            ->label('Meta: Reference #')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.status')
                            ->label('Meta: Status')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.submitted_at')
                            ->label('Meta: Submitted At')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.processed_at')
                            ->label('Meta: Processed At')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.assigned_table_room.name')
                            ->label('Meta: Assigned Table/Room')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.coupon.code')
                            ->label('Meta: Coupon Code')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data.guest_checkout.coupon.discount_amount')
                            ->label('Meta: Coupon Discount')
                            ->money('PHP')
                            ->placeholder('N/A'),

                        TextEntry::make('meta_data')
                            ->label('Meta Data')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record): string => json_encode($record->meta_data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->copyable()
                            ->fontFamily('mono'),
                    ])
                    ->columns(2),
            ]);
    }
}
