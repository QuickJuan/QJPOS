<?php
namespace App\Filament\Tenant\Resources\TableReservationResource\Pages;

use App\Filament\Tenant\Resources\TableReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTableReservation extends ViewRecord
{
    protected static string $resource = TableReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.table-reservations.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Table Room Details')
                    ->schema([
                        TextEntry::make('tableRoom.name')
                            ->label('Table Room'),

                        TextEntry::make('user.name')
                            ->label('User'),
                    ])
                    ->columns(2),

                Section::make('Reservation Details')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('reservation_from')
                            ->label('Reservation From')
                            ->dateTime('F d Y, h:i A'),

                        TextEntry::make('reservation_to')
                            ->label('Reservation To')
                            ->dateTime('F d Y, h:i A'),

                        TextEntry::make('status')
                            ->label('Status'),
                    ]),

                Section::make('Contact Information')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Customer Name'),

                        TextEntry::make('pax')
                            ->label('Number of Pax'),

                        TextEntry::make('contact_phone')
                            ->label('Contact Phone'),

                        TextEntry::make('contact_email')
                            ->label('Contact Email'),

                        TextEntry::make('notes')
                            ->label('Notes'),
                    ]),
            ]);
    }
}
