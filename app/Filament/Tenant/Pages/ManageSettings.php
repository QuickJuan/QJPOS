<?php

namespace App\Filament\Tenant\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

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
                    ->maxSize(2048),
            ]);
    }
}
