<?php

namespace App\Filament\Widgets;

use App\Models\Cron;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CronStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Add interactive stat cards

        $total = Cron::count();
        $active = Cron::where('is_active', true)->count();
        $inactive = Cron::where('is_active', false)->count();

        return [
            Stat::make('Total Crons', $total)->color('primary'),
            Stat::make('Active Crons', $active)->color('success'),
            Stat::make('Inactive Crons', $inactive)->color('danger'),
        ];
    }
}
