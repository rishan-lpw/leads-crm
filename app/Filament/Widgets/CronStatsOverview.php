<?php

namespace App\Filament\Widgets;

use App\Models\Cron;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CronStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Cron::count();
        $active = Cron::where('is_active', true)->count();
        $inactive = Cron::where('is_active', false)->count();

        return [
            Stat::make('Total Crons', $total)
                ->color('primary')
                ->description('All registered crons')
                ->descriptionIcon(''),
            Stat::make('Active Crons', $active)
                ->color('success')
                ->description('Enabled')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),
            Stat::make('Inactive Crons', $inactive)
                ->color('danger')
                ->description('Disabled')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),
        ];
    }

    protected function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CronRunsChart::class,
        ];
    }
}
