<?php
namespace App\Filament\Tenant\Resources\CartResource\Pages;

use App\Filament\Tenant\Resources\CartResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                        TextEntry::make('cashier.name'),

                        TextEntry::make('cashierSession.beginnning_cash')
                            ->label('Cashier Session Beginning Cash'),
                    ])
                    ->columns(2),

                Section::make('')
                    ->schema([
                        TextEntry::make('notes'),

                        RepeatableEntry::make('meta_data')
                            ->label('Meta Data')
                            ->schema([
                                TextEntry::make('data')
                                    ->markdown(),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
