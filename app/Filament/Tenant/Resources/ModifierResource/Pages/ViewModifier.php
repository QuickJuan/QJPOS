<?php
namespace App\Filament\Tenant\Resources\ModifierResource\Pages;

use App\Filament\Tenant\Resources\ModifierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewModifier extends ViewRecord
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.modifiers.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Modifier Details')
                    ->schema([
                        TextEntry::make('product.name'),
                        TextEntry::make('name'),
                    ])
                    ->columns(2),

                Section::make('Lists')
                    ->schema([
                        TextEntry::make('list')
                            ->label('Items')
                            ->formatStateUsing(fn($state) =>
                                collect(explode(',', $state))
                                    ->map(fn($item) => trim($item))
                                    ->implode('<br>')
                            )
                            ->html(),
                    ])
                    ->columns(2),
            ]);
    }
}
