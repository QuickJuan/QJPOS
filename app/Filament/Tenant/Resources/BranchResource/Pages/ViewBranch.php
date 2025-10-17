<?php
namespace App\Filament\Tenant\Resources\BranchResource\Pages;

use App\Filament\Tenant\Resources\BranchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewBranch extends ViewRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.branches.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Branch Details')
                    ->schema([
                        TextEntry::make('branch_code')
                            ->label('Branch Code'),

                        TextEntry::make('name')
                            ->label('Branch Name'),

                        TextEntry::make('address')
                            ->label('Address'),

                        TextEntry::make('phone')
                            ->label('Phone'),

                        TextEntry::make('email')
                            ->label('Email'),

                        TextEntry::make('contact_person')
                            ->label('Contact Person'),

                        TextEntry::make('long_lat')
                            ->label('Longitude/Latitude'),

                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),

                        TextEntry::make('tin')
                            ->label('TIN'),
                    ])
                    ->columns(3),

                Section::make('Associated Users')
                    ->schema([
                        TextEntry::make('users')
                            ->label('Users')
                            ->formatStateUsing(fn($record) =>
                                $record->users
                                    ->unique('id')
                                    ->pluck('name')
                                    ->implode('<br>')
                            )
                            ->html(),
                    ]),
            ]);
    }
}
