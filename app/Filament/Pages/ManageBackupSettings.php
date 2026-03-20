<?php

namespace App\Filament\Pages;

use App\Settings\BackupSettings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SettingsPage;

class ManageBackupSettings extends SettingsPage
{
    protected static ?string $navigationIcon  = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationLabel = 'Backup Storage';
    protected static ?string $navigationGroup = 'Central Management';
    protected static ?int    $navigationSort  = 4;

    protected static string $settings = BackupSettings::class;

    public function getTitle(): string
    {
        return 'Backup Storage Settings';
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Storage Driver')
                ->description('Choose where asset backups are saved. S3-compatible drivers (AWS S3, DigitalOcean Spaces) store backups in the cloud.')
                ->schema([
                    Select::make('disk_driver')
                        ->label('Storage Driver')
                        ->options([
                            'local'     => 'Local (server disk)',
                            's3'        => 'AWS S3',
                            'do-spaces' => 'DigitalOcean Spaces',
                        ])
                        ->default('local')
                        ->required()
                        ->live()
                        ->helperText(fn (Get $get) => match ($get('disk_driver')) {
                            's3'        => 'Backups will be uploaded to your AWS S3 bucket.',
                            'do-spaces' => 'Backups will be uploaded to your DigitalOcean Spaces bucket.',
                            default     => 'Backups are saved to storage/app/asset-backups on this server.',
                        }),
                ]),

            Section::make('S3 / Spaces Credentials')
                ->description('Credentials for your cloud storage bucket. These are stored securely in the tenant database.')
                ->hidden(fn (Get $get) => $get('disk_driver') === 'local')
                ->schema([
                    TextInput::make('s3_key')
                        ->label('Access Key')
                        ->placeholder('AKIAIOSFODNN7EXAMPLE')
                        ->required(fn (Get $get) => $get('disk_driver') !== 'local')
                        ->maxLength(255),

                    TextInput::make('s3_secret')
                        ->label('Secret Key')
                        ->password()
                        ->revealable()
                        ->placeholder('••••••••••••••••••••••••••••••••')
                        ->required(fn (Get $get) => $get('disk_driver') !== 'local')
                        ->maxLength(500),

                    TextInput::make('s3_region')
                        ->label('Region')
                        ->placeholder(fn (Get $get) => $get('disk_driver') === 'do-spaces' ? 'nyc3' : 'us-east-1')
                        ->helperText(fn (Get $get) => $get('disk_driver') === 'do-spaces'
                            ? 'DigitalOcean Spaces region slug, e.g. nyc3, ams3, sgp1, sfo3'
                            : 'AWS region, e.g. us-east-1, ap-southeast-1')
                        ->required(fn (Get $get) => $get('disk_driver') !== 'local')
                        ->maxLength(100),

                    TextInput::make('s3_bucket')
                        ->label('Bucket Name')
                        ->placeholder('my-app-backups')
                        ->required(fn (Get $get) => $get('disk_driver') !== 'local')
                        ->maxLength(255),

                    TextInput::make('s3_endpoint')
                        ->label('Endpoint URL')
                        ->url()
                        ->placeholder('https://nyc3.digitaloceanspaces.com')
                        ->helperText('Required for DigitalOcean Spaces. Not needed for AWS S3.')
                        ->visible(fn (Get $get) => $get('disk_driver') === 'do-spaces')
                        ->required(fn (Get $get) => $get('disk_driver') === 'do-spaces')
                        ->maxLength(255),

                    TextInput::make('s3_prefix')
                        ->label('Folder / Path Prefix')
                        ->placeholder('asset-backups')
                        ->helperText('The folder inside the bucket where backups will be stored. Leave blank to use the bucket root.')
                        ->default('asset-backups')
                        ->maxLength(255),
                ]),

            Section::make('Local Storage Path')
                ->description('Customize where on this server backups are saved. Leave blank to use the default location.')
                ->hidden(fn (Get $get) => $get('disk_driver') !== 'local')
                ->schema([
                    TextInput::make('local_path')
                        ->label('Backup Directory Path')
                        ->placeholder(storage_path('app/asset-backups'))
                        ->helperText('Absolute path (e.g. /mnt/nas/backups/quickjuan) or path relative to the project root. Leave blank to use the default: storage/app/asset-backups. The directory will be created automatically if it does not exist.')
                        ->maxLength(500)
                        ->suffixIcon('heroicon-m-folder'),
                ]),

            Section::make('Local Storage Info')
                ->hidden(fn (Get $get) => $get('disk_driver') !== 'local')
                ->schema([
                    Placeholder::make('local_info')
                        ->label('')
                        ->content('Make sure the configured directory is writable by the web server and is included in your server-level backup schedule.'),
                ]),

        ]);
    }
}
