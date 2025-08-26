<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\CustomerActivityChart;
use App\Filament\Widgets\CronRunsChart;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            CustomerActivityChart::class,
            CronRunsChart::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
