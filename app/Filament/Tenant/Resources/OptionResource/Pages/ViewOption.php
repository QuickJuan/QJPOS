<?php
namespace App\Filament\Tenant\Resources\OptionResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Tenant\Resources\OptionResource;

class ViewOption extends ViewRecord
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.options.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Option Details')
                    ->schema([
                        TextEntry::make('option_name')
                            ->label('Option Name'),

                        TextEntry::make('product.name')
                            ->label('Product')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('max_quantity')
                            ->label('Max Quantity')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('is_default')
                            ->label('Is Default')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                            ->color(fn ($state) => $state ? 'success' : 'gray'),

                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
