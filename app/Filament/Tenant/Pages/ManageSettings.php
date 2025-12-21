<?php

namespace App\Filament\Tenant\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
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
            ]);
    }
}
