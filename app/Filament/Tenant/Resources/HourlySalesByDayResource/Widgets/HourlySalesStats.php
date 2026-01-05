<?php

namespace App\Filament\Tenant\Resources\HourlySalesByDayResource\Widgets;

use App\Models\HourlySalesByDay;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HourlySalesStats extends BaseWidget
{
    protected function getStats(): array
    {
        $data = HourlySalesByDay::selectRaw('
            SUM(monday_sales) as monday,
            SUM(tuesday_sales) as tuesday,
            SUM(wednesday_sales) as wednesday,
            SUM(thursday_sales) as thursday,
            SUM(friday_sales) as friday,
            SUM(saturday_sales) as saturday,
            SUM(sunday_sales) as sunday,
            SUM(total_sales) as total
        ')->first();

        return [
            Stat::make('Monday', '₱' . number_format($data->monday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Tuesday', '₱' . number_format($data->tuesday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Wednesday', '₱' . number_format($data->wednesday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Thursday', '₱' . number_format($data->thursday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Friday', '₱' . number_format($data->friday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Saturday', '₱' . number_format($data->saturday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Sunday', '₱' . number_format($data->sunday ?? 0, 2))
                ->color('success')
                ->icon('heroicon-o-calendar'),
            Stat::make('Total Sales', '₱' . number_format($data->total ?? 0, 2))
                ->description('All Days Combined')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
