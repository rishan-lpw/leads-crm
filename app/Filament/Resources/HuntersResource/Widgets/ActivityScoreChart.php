<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class ActivityScoreChart extends ChartWidget
{
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
        ];
    }

    // getData
    protected function getData(): array
    {
        return [
            'labels' => ['Activity 1', 'Activity 2', 'Activity 3'],
            'datasets' => [
                [
                    'label' => 'Activity Score',
                    'data' => [10, 20, 30],
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ],
            ],
        ];
    }
}
