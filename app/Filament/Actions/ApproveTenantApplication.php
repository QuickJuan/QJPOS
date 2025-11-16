<?php

namespace App\Filament\Actions;

use App\Mail\TenantApplicationApproved;
use App\Models\Central\TenantApplication;
use App\Models\Central\Tenant;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class ApproveTenantApplication extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve_application';
    }

    public static function make(?string $name = null): static
    {
        $name ??= static::getDefaultName();
        $action = parent::make($name);
        $action
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->form([
                Section::make('Tenant Setup Information')
                    ->description('Configure the subdomain and credentials for this tenant')
                    ->schema([
                        TextInput::make('subdomain')
                            ->label('Subdomain')
                            ->required()
                            ->placeholder('juans-coffee'),
                        TextInput::make('database_name')
                            ->label('Database Name')
                            ->required()
                            ->placeholder('tenant_juans_coffee_db_1125'),
                    ]),
            ])
            ->action(static function (TenantApplication $record, array $data): void {
                // Clean any stray output before processing
                if (ob_get_level()) {
                    ob_clean();
                }

                $success = false;
                $message = '';

                try {
                    // Generate temporary password
                    $temporaryPassword = Str::random(12);

                    // Use the database name from form
                    $databaseName = $data['database_name'];

                    // Create tenant in database
                    $tenant = Tenant::create([
                        'tenancy_db_name' => $databaseName,
                        'email' => $record->owner_email,
                        'name' => $record->business_name,
                        'phone' => $record->owner_phone,
                        'address' => $record->business_address,
                    ]);

                    // Create domain for tenant
                    $tenant->domains()->create([
                        'domain' => $data['subdomain'],
                    ]);

                    // Update application status
                    $record->update([
                        'status' => 'approved',
                        'notes' => 'Approved on ' . ' Subdomain: ' . $data['subdomain'] . ' Database: ' . $databaseName,
                    ]);

                    // Send approval email
                    Mail::to($record->owner_email)->send(
                        new TenantApplicationApproved(
                            $record,
                            $data['subdomain'],
                            $record->owner_email,
                            $temporaryPassword
                        )
                    );

                    $success = true;
                    $message = 'Application approved and email sent';
                } catch (\Exception $e) {
                    $success = false;
                    $message = 'Failed: Contact administrator';
                }

                // Clean any output from tenant creation before sending response
                if (ob_get_level()) {
                    ob_clean();
                }

                if ($success) {
                    Notification::make()
                        ->title('Success')
                        ->body($message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error')
                        ->body($message)
                        ->danger()
                        ->send();
                }
            });

        return $action;
    }
}
