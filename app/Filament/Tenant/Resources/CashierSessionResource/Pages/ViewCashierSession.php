<?php

namespace App\Filament\Tenant\Resources\CashierSessionResource\Pages;

use App\Filament\Tenant\Resources\CashierSessionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;

class ViewCashierSession extends ViewRecord
{
    protected static string $resource = CashierSessionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Session Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch'),
                                TextEntry::make('business_date')
                                    ->label('Business Date')
                                    ->date('M d, Y'),
                                TextEntry::make('cashier.name')
                                    ->label('Cashier'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('started_time')
                                    ->label('Started Time')
                                    ->dateTime('M d, Y h:i A'),
                                TextEntry::make('closing_time')
                                    ->label('Closing Time')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Still Open'),
                            ]),
                    ]),

                Section::make('Financial Summary')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('beginning_cash')
                                    ->label('Beginning Cash')
                                    ->money('php')
                                    ->color('info'),
                                TextEntry::make('total_sales')
                                    ->label('Total Sales')
                                    ->money('php')
                                    ->color('success')
                                    ->weight('bold'),
                                TextEntry::make('closing_cash')
                                    ->label('Closing Cash')
                                    ->money('php')
                                    ->color('warning'),
                            ]),
                    ]),

                Section::make('Cash Denomination')
                    ->schema([
                        KeyValueEntry::make('cash_denomination')
                            ->label('Denomination Breakdown')
                            ->keyLabel('Denomination')
                            ->valueLabel('Count')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->cash_denomination)),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->cash_denomination)),

                Section::make('Meta Data')
                    ->schema([
                        KeyValueEntry::make('meta_data')
                            ->label('Session Details')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->meta_data)),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->meta_data)),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('checkBy.name')
                            ->label('Checked By')
                            ->default('Not Yet Checked'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->default('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
