<?php

namespace App\Filament\Resources\Central\TenantResource\Pages;

use App\Filament\Resources\Central\TenantResource;
use Filament\Resources\Pages\CreateRecord;
use Exception;
use Illuminate\Support\Facades\Redirect;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected ?string $domainInput = null;

    protected bool $shouldSendSuccessNotification = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clean any stray output
        if (ob_get_level()) {
            ob_clean();
        }

        $this->domainInput = $data['domain'] ?? null;
        unset($data['domain']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if (!$this->record || !$this->domainInput) {
            return;
        }

        try {
            $this->record->domains()->create([
                'domain' => $this->domainInput,
            ]);
        } catch (Exception $e) {
            \Log::error('Domain creation error: ' . $e->getMessage());
        }

        // Clean any output from tenant creation
        if (ob_get_level()) {
            ob_clean();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        // Use the resource's index URL
        return $this->getResource()::getUrl('index');
    }
}
