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

                        TextEntry::make('registration_number')
                            ->label('Registration Number'),

                        TextEntry::make('or_number')
                            ->label('OR Number'),

                        TextEntry::make('bill_no')
                            ->label('Bill Number'),

                        TextEntry::make('order_number')
                            ->label('Order Number'),
                    ])
                    ->columns(3),

                Section::make('Receipt Configuration')
                    ->schema([
                        TextEntry::make('receipt_headers')
                            ->label('Receipt Headers')
                            ->formatStateUsing(function ($state, $record) {
                                // Debug: Let's see what the actual data looks like
                                $headers = $record->receipt_headers;

                                if (empty($headers)) {
                                    return 'No receipt headers configured';
                                }

                                // Handle different possible data structures
                                if (is_string($headers)) {
                                    $headers = json_decode($headers, true);
                                }

                                if (!is_array($headers)) {
                                    return 'No receipt headers configured';
                                }

                                // If it's an array of objects with 'header' key (from simple repeater)
                                if (isset($headers[0]) && is_array($headers[0]) && isset($headers[0]['header'])) {
                                    $headerStrings = array_column($headers, 'header');
                                } else {
                                    // If it's already a flat array of strings
                                    $headerStrings = $headers;
                                }

                                return implode('<br>', array_filter($headerStrings));
                            })
                            ->html()
                            ->placeholder('No receipt headers configured'),
                    ]),

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
