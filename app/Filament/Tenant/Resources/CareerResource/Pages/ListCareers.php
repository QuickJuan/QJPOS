<?php

namespace App\Filament\Tenant\Resources\CareerResource\Pages;

use App\Filament\Tenant\Resources\CareerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCareers extends ListRecords
{
    protected static string $resource = CareerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'available' => Tab::make('Available')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'available')),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed')),
        ];
    }
}
