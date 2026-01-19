<?php

namespace App\Filament\Tenant\Resources\PageResource\Pages;

use App\Filament\Tenant\Resources\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview')
                ->label('Preview')
                ->url(function () {
                    if ($this->record->page_type === 'landing_page') {
                        return '/';
                    }

                    $path = $this->record->getFullUrlPath();
                    return $path ? "/{$path}" : null;
                })
                ->visible(fn () => $this->record->slug || $this->record->page_type === 'landing_page')
                ->icon('heroicon-o-eye')
                ->openUrlInNewTab(),

            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
