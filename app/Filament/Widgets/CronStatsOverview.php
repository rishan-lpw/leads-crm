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
        

        return [
            Stat::make('Total Crons', $total)
                ->color('primary')
                ->description('All registered crons')
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
