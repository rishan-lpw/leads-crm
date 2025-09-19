<?php

namespace App\Filament\Resources\AgentDashboardResource\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentActivitiesStats extends ChartWidget
{
    protected ?string $heading = 'Agent Activities Stats';
    protected ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        return [
            // Data fetching from activities table
            'labels' => ['Activity 1', 'Activity 2', 'Activity 3'],
            'datasets' => [
                [
                    'data' => [10, 20, 30],
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56'],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    // Override the getStats method to provide specific stats for this widget
    protected function getStats(): array
    {
        return [
            Stat::make('Total Activities', 60)
                ->description('Total activities performed by agents'),
            Stat::make('Pending Activities', 10)
                ->description('Activities that are still pending'),
            Stat::make('Completed Activities', 50)
                ->description('Activities that have been completed'),
        ];
    }
}
