<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;

class ActivitiesByTypeChart extends ChartWidget
{
    protected ?string $heading = 'Activities by Type (30d)';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $rows = Activity::query()
            ->selectRaw('COALESCE(activity_type, "other") as type, COUNT(*) as count')
            ->where('date_time', '>=', now()->subDays(30))
            ->groupBy('type')
            ->pluck('count', 'type');

        if ($rows->isEmpty()) {
            return [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Activities',
                        'data' => [0],
                        'backgroundColor' => ['#E5E7EB'],
                    ],
                ],
            ];
        }

        $labels = $rows->keys()->map(fn ($k) => ucfirst((string) $k))->values()->all();
        $data = $rows->values()->map(fn ($v) => (int) $v)->all();
        $colors = [
            '#60A5FA', '#34D399', '#FBBF24', '#F87171', '#A78BFA', '#F472B6', '#10B981', '#F59E0B', '#3B82F6', '#93C5FD',
        ];

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'backgroundColor' => array_slice(array_merge($colors, $colors), 0, count($data)),
                ],
            ],
        ];
    }
}


