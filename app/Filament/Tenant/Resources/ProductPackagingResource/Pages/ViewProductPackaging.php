<?php
namespace App\Filament\Tenant\Resources\ProductPackagingResource\Pages;

use App\Filament\Tenant\Resources\ProductPackagingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProductPackaging extends ViewRecord
{
    protected static string $resource = ProductPackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.product-packagings.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Attendance Details')
                    ->schema([
                        TextEntry::make('product.name'),

                        TextEntry::make('barcode')
                            ->label('Barcode / SKU'),

                        TextEntry::make('cost')
                            ->label('Cost'),

                        TextEntry::make('price')
                            ->label('Price'),

                        TextEntry::make('unit_measure')
                            ->label('Unit Measure'),

                        TextEntry::make('qty')
                            ->label('Quantity'),

                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square(),
                    ])
                    ->columns(3),
            ]);
    }
}
