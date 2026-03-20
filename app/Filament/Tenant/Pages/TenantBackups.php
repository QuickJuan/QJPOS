<?php

namespace App\Filament\Tenant\Pages;

use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups;

class TenantBackups extends Backups
{
    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 98; // just before DatabaseCleanup (99)

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }
}
