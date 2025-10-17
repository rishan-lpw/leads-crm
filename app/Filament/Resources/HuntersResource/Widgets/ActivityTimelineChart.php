<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class ActivityTimelineChart extends ChartWidget
{
    // protected static ?string $heading = 'Activity Timeline (Last 30 Days)';

    // protected static ?string $maxHeight = '300px';

    public ?Model $record = null;

    protected function getData(): array
    {
        if (! $this->record) {
            return ['datasets' => [], 'labels' => []];
        }

        $activities = $this->record->activities()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[] = $activities->get($date)->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
