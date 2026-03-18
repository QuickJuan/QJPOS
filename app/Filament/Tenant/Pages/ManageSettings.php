<?php

namespace App\Filament\Tenant\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use League\Flysystem\UnableToCheckFileExistence;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('company_name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('company_address')
                    ->label('Company Address')
                    ->maxLength(500),

                TextInput::make('company_contact')
                    ->label('Company Contact')
                    ->maxLength(255),

                Textarea::make('contact_recipient_emails')
                    ->label('Contact form recipients (CSV)')
                    ->helperText('Comma-separated email addresses that will receive contact form submissions.')
                    ->rows(2),

                Select::make('timezone')
                    ->label('Timezone')
                    ->options([
                        'UTC' => 'UTC (GMT+0)',
                        'Asia/Manila' => 'Philippines (GMT+8)',
                        'America/New_York' => 'Eastern Time (GMT-5)',
                        'America/Chicago' => 'Central Time (GMT-6)',
                        'America/Denver' => 'Mountain Time (GMT-7)',
                        'America/Los_Angeles' => 'Pacific Time (GMT-8)',
                        'Europe/London' => 'London (GMT+0)',
                        'Europe/Paris' => 'Central European Time (GMT+1)',
                        'Asia/Tokyo' => 'Tokyo (GMT+9)',
                        'Asia/Shanghai' => 'Shanghai (GMT+8)',
                        'Asia/Hong_Kong' => 'Hong Kong (GMT+8)',
                        'Asia/Singapore' => 'Singapore (GMT+8)',
                        'Australia/Sydney' => 'Sydney (GMT+10)',
                        'Pacific/Auckland' => 'Auckland (GMT+12)',
                    ])
                    ->default('Asia/Manila')
                    ->required(),

                FileUpload::make('company_logo')
                    ->image()
                    ->directory('settings')
                    ->disk('public')
                    ->preserveFilenames()
                    ->maxSize(2048)
                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string | array | null $storedFileNames): ?array {
                        $storage = $component->getDisk();
                        $shouldFetchFileInformation = $component->shouldFetchFileInformation();

                        if ($shouldFetchFileInformation) {
                            try {
                                if (! $storage->exists($file)) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence) {
                                return null;
                            }
                        }

                        return [
                            'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                            'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                            'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                            'url' => tenant_asset($file),
                        ];
                    }),

                FileUpload::make('hero_image')
                    ->label('Tenant hero banner')
                    ->image()
                    ->directory('settings/hero-images')
                    ->disk('public')
                    ->preserveFilenames()
                    ->maxSize(5120)
                    ->helperText('Best at 1920×1080 or wider for full-bleed hero displays.')
                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string | array | null $storedFileNames): ?array {
                        $storage = $component->getDisk();
                        $shouldFetchFileInformation = $component->shouldFetchFileInformation();

                        if ($shouldFetchFileInformation) {
                            try {
                                if (! $storage->exists($file)) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence) {
                                return null;
                            }
                        }

                        return [
                            'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                            'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                            'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                            'url' => tenant_asset($file),
                        ];
                    })
                    ->nullable(),

                TextInput::make('points_earning_rate')
                    ->label('Points Earning Rate')
                    ->helperText('Amount customer needs to spend to earn 1 point (e.g., 100 means ₱100 = 1 point)')
                    ->numeric()
                    ->default(100)
                    ->minValue(1)
                    ->required()
                    ->suffix('per point'),
            ]);
    }
}
