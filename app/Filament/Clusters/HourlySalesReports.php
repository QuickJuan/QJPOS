<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class HourlySalesReports extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Hourly Sales';
    protected static ?string $navigationGroup = 'Order Reports';
    protected static ?int $navigationSort = 10;
}
