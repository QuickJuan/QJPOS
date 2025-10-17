<?php
namespace App\Filament\Tenant\Resources\DiscountResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Tenant\Resources\DiscountResource;

class ViewDiscount extends ViewRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.discounts.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Discount Details')
                    ->schema([
                        TextEntry::make('discount_name')
                            ->label('Discount Name'),

                        TextEntry::make('amount'),

                        TextEntry::make('type'),

                        IconEntry::make('remove_tax')
                            ->boolean(),

                        TextEntry::make('discount_type'),

                        IconEntry::make('require_customer_info')
                            ->boolean(),
                    ])
                    ->columns(3),
            ]);
    }
}
