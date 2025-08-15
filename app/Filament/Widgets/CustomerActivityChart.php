<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Activity Trends';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '15s';
    
    protected function getData(): array
    {
        $data = Activity::select('activity_type', DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('activity_type')
            ->get()
            ->toArray();
            
        $labels = [];
        $values = [];
        
        foreach ($data as $item) {
            $labels[] = $item['activity_type'] ?? 'Other';
            $values[] = $item['count'];
        }
        
        // If we have no data, provide some dummy data
        if (empty($labels)) {
            $labels = ['Call', 'Email', 'Meeting', 'Follow-up', 'Sale'];
            $values = [15, 25, 10, 8, 12];
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}
