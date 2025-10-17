<?php
namespace App\Filament\Tenant\Resources\CategoryResource\Pages;

use App\Filament\Tenant\Resources\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.categories.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Category Details')
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
