<?php
namespace App\Filament\Tenant\Resources\CouponCodeResource\Pages;

use App\Filament\Tenant\Resources\CouponCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCouponCode extends ViewRecord
{
    protected static string $resource = CouponCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.coupon-codes.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('code'),
                            ]),

                        TextEntry::make('description'),
                    ]),

                Section::make('Discount Configuration')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type'),
                                TextEntry::make('value'),
                                TextEntry::make('minimum_amount'),
                            ]),
                    ]),

                Section::make('Usage Limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('usage_limit'),
                                TextEntry::make('usage_limit_per_user'),
                            ]),
                    ]),

                Section::make('Validity Period')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('valid_from')
                                    ->label('Valid From')
                                    ->dateTime('F d Y, h:i:s A'),

                                TextEntry::make('valid_until')
                                    ->label('Valid Until')
                                    ->dateTime('F d Y, h:i:s A'),
                            ]),
                    ]),

                Section::make('Product & Category Restrictions')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('applicable_products')
                                    ->label('Applicable Products'),

                                TextEntry::make('applicable_categories')
                                    ->label('Applicable Categories'),
                            ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ]),
            ]);
    }
}
