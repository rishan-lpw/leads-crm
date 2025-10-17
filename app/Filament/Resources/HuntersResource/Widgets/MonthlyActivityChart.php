<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonthlyActivityChart extends ChartWidget
{
    // protected static ?string $heading = 'Monthly Activity Comparison';

    // protected static ?string $maxHeight = '300px';

    public ?Model $record = null;

    protected function getData(): array
    {
        if (! $this->record) {
            return ['datasets' => [], 'labels' => []];
        }

        $activities = $this->record->activities()
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT));

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            $data[] = $activities->get($key)->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'backgroundColor' => [
                        '#10B981', '#3B82F6', '#FBBF24', '#EF4444', '#8B5CF6', '#EC4899',
                    ],
                    'borderColor' => '#E5E7EB',
                    'borderWidth' => 2,
                    'borderRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
