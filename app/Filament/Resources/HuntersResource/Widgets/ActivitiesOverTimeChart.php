<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;

class ActivitiesOverTimeChart extends ChartWidget
{
    public ?int $leadId = null;
    
    public function mount(?int $leadId = null): void
    {
        $this->leadId = $leadId;
    }
    
    public function getHeading(): string
    {
        return 'Activities Over Time (30d)' . ($this->leadId ? " - Lead #{$this->leadId}" : '');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $query = Activity::query()
            ->selectRaw('DATE(COALESCE(date_time, created_at)) as d, COUNT(*) as c')
            ->where(function($q) {
                $q->where('date_time', '>=', now()->subDays(30))
                  ->orWhere('created_at', '>=', now()->subDays(30));
            });
        
        // Filter by lead if leadId is provided
        if ($this->leadId) {
            $query->where('lead_id', $this->leadId);
        }
        
        $rows = $query->groupBy('d')
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


