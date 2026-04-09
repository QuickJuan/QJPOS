<?php

namespace App\Filament\Tenant\Resources\CustomerFeedbackResource\Pages;

use App\Enums\CustomerFeedbackStatus;
use App\Filament\Tenant\Resources\CustomerFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerFeedback extends ViewRecord
{
    protected static string $resource = CustomerFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publish')
                ->label('Publish')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->status !== CustomerFeedbackStatus::Published)
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => CustomerFeedbackStatus::Published]))
                ->after(fn () => $this->refreshFormData(['status'])),

            Actions\Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->status !== CustomerFeedbackStatus::Rejected)
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => CustomerFeedbackStatus::Rejected]))
                ->after(fn () => $this->refreshFormData(['status'])),

            Actions\Action::make('pending')
                ->label('Mark as Pending')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->visible(fn () => $this->record->status !== CustomerFeedbackStatus::Pending)
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => CustomerFeedbackStatus::Pending]))
                ->after(fn () => $this->refreshFormData(['status'])),

            Actions\EditAction::make(),
        ];
    }
}
