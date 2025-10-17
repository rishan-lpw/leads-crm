<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class ActivityTypeChart extends ChartWidget
{
    // protected static ?string $heading = 'Activity Distribution by Type';

    // protected static ?string $maxHeight = '300px';

    public ?Model $record = null;

    protected function getData(): array
    {
        if (! $this->record) {
            return ['datasets' => [], 'labels' => []];
        }

        $activityTypes = $this->record->activities()
            ->selectRaw('COALESCE(stage, "Unknown") as stage, COUNT(*) as count')
            ->groupBy('stage')
            ->get();

        if ($activityTypes->isEmpty()) {
            return ['datasets' => [], 'labels' => ['No Data']];
        }

        $labels = $activityTypes->pluck('stage')->map(fn($s) => ucfirst($s))->toArray();
        $data = $activityTypes->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Activity Count',
                    'data' => $data,
                    'backgroundColor' => [
                        '#F87171', '#60A5FA', '#34D399',
                        '#FBBF24', '#A78BFA', '#F472B6',
                        '#FACC15', '#22D3EE',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
