<?php
namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.products.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Product Details')
                    ->schema([
                        TextEntry::make('category.name'),

                        TextEntry::make('brand.name'),

                        TextEntry::make('preparationLocation.description'),

                        TextEntry::make('uuid')
                            ->label('UUID'),

                        TextEntry::make('name')
                            ->label('Product Name'),

                        TextEntry::make('receipt_alias')
                            ->label('Receipt Name'),

                        TextEntry::make('description')
                            ->label('Description'),
                    ])
                    ->columns(4),

                Section::make('Images')
                    ->schema([
                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square(),

                        ImageEntry::make('product_images')
                            ->label('Product Images')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('product_images'))
                            ->square(),
                    ]),

                Section::make('Associated Groups')
                    ->schema([
                        TextEntry::make('groups')
                            ->label('Groups')
                            ->formatStateUsing(fn($record) =>
                                $record->groups
                                    ->unique('id')
                                    ->pluck('name')
                                    ->implode('<br>')
                            )
                            ->html(),
                    ]),

                Section::make('Associated Options')
                    ->schema([
                        TextEntry::make('options')
                            ->label('Options')
                            ->formatStateUsing(fn($record) =>
                                $record->options
                                    ->unique('id')
                                    ->pluck('option_name')
                                    ->implode('<br>')
                            )
                            ->html(),
                    ]),
            ]);
    }
}
