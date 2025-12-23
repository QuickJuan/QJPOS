<?php

namespace App\Filament\Tenant\Resources\InventoryStockMovementResource\Pages;

use App\Filament\Tenant\Resources\InventoryStockMovementResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInventoryStockMovement extends ViewRecord
{
    protected static string $resource = InventoryStockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Movement Details')
                    ->schema([
                        TextEntry::make('inventory.name')->label('Inventory'),
                        TextEntry::make('location.location')->label('Location'),
                        TextEntry::make('movement_type')
                            ->label('Type')
                            ->formatStateUsing(fn (string $state) => $state === 'in' ? 'Stock In' : 'Stock Out'),
                        TextEntry::make('quantity')
                            ->label('Quantity (Base)')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                        TextEntry::make('packaging.name')
                            ->label('Packaging')
                            ->placeholder('-'),
                        TextEntry::make('resulting_stock')
                            ->label('Balance After Movement')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                        TextEntry::make('user.name')->label('Recorded By'),
                        TextEntry::make('notes')->label('Notes')->columnSpan(2),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y h:i A'),
                    ])
                    ->columns(2),
            ]);
    }
}
