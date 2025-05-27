<?php

namespace App\Filament\Tenant\Resources\BranchResource\Pages;

use App\Filament\Tenant\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;
}
