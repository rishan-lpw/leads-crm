<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use App\Models\Funnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FunnelChart extends ChartWidget
{
    protected ?string $heading = 'Funnel Progress';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Activity::query()
            ->select('funnel_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('funnel_id')
            ->where('date_time', '>=', now()->subDays(90))
            ->groupBy('funnel_id')
            ->pluck('count', 'funnel_id');

        if ($rows->isEmpty()) {
            return [
                'labels' => ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Activities',
                        'data' => [0],
                        'backgroundColor' => ['#E5E7EB'], // neutral gray color
                        'borderWidth' => 1,
                    ],
                ],
            ];
        }

        $funnels = Funnel::whereIn('id', $rows->keys())->get()->keyBy('id');

        $labels = [];
        $data = [];

        foreach ($rows as $funnelId => $count) {
            $f = $funnels->get($funnelId);
            $label = $f
                ? ucfirst($f->category) . ' - Stage ' . $f->stage
                : 'Funnel #' . $funnelId;
            $labels[] = $label;
            $data[] = (int) $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'backgroundColor' => array_fill(0, count($data), '#60A5FA'),
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}

