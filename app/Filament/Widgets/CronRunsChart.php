<?php

namespace App\Filament\Widgets;

use App\Models\CronLog;
use Filament\Widgets\LineChartWidget;

class CronRunsChart extends LineChartWidget
{
    protected ?string $heading = 'Cron Runs (Last 14 Days)';

    protected function getData(): array
    {
        $dates = collect(range(0, 13))->map(function ($i) {
            return now()->subDays(13 - $i)->format('Y-m-d');
        });

        $runs = $dates->map(function ($date) {
            return CronLog::whereDate('started_at', $date)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Runs',
                    'data' => $runs,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.2)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
}
