<?php

namespace App\Filament\Resources\Central\UserResource\Pages;

use App\Filament\Resources\Central\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
