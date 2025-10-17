<?php
namespace App\Filament\Tenant\Resources\BrandResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Tenant\Resources\BrandResource;

class ViewBrand extends ViewRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.brands.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Brand Details')
                    ->schema([
                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square(),

                        TextEntry::make('name'),
                    ])
                    ->columns(1),
            ]);
    }
}
