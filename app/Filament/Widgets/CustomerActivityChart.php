<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Activities (Last 30 Days)';

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $activities = Activity::select(
            DB::raw('DATE(date_time) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('date_time', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

        $labels = [];
        $data = [];

        // Create a date range for the last 30 days
        $dateRange = collect();
        for ($i = 29; $i >= 0; $i--) {
            $dateRange->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        foreach ($dateRange as $date) {
            $labels[] = Carbon::parse($date)->format('M d');
            $activity = $activities->firstWhere('date', $date);
            $data[] = $activity ? $activity->count : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
