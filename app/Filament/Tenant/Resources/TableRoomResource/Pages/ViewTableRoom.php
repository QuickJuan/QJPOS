<?php
namespace App\Filament\Tenant\Resources\TableRoomResource\Pages;

use App\Filament\Tenant\Resources\TableRoomResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTableRoom extends ViewRecord
{
    protected static string $resource = TableRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.table-rooms.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Table Room Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch'),

                                TextEntry::make('name')
                                    ->label('Table/Room Name'),

                                TextEntry::make('chairs')
                                    ->label('Number of Chairs'),

                                TextEntry::make('merge_to')
                                    ->label('Merge To'),

                                TextEntry::make('status')
                                    ->label('Status'),

                                TextEntry::make('with_timeframe')
                                    ->label('Time monitoring'),

                            ])
                            ->columns(3),
                    ]),

                Section::make('Table Properties')
                    ->schema([
                        TextEntry::make('table_width')
                            ->label('Table Width'),

                        TextEntry::make('table_height')
                            ->label('Table Height'),

                        TextEntry::make('table_x')
                            ->label('Table X Coordinate'),

                        TextEntry::make('table_y')
                            ->label('Table Y Coordinate'),

                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square(),

                    ])
                    ->columns(2),
            ]);
    }
}
