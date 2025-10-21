<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;

class ActivitiesOverTimeChart extends ChartWidget
{
    protected ?string $heading = 'Activities Over Time (30d)';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $rows = Activity::query()
            ->selectRaw('DATE(date_time) as d, COUNT(*) as c')
            ->where('date_time', '>=', now()->subDays(30))
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd');

        if ($rows->isEmpty()) {
            return [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Activities',
                        'data' => [0],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'tension' => 0.3,
                    ],
                ],
            ];
        }

        return [
            'labels' => array_keys($rows->toArray()),
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => array_values($rows->map(fn ($v) => (int) $v)->toArray()),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}


