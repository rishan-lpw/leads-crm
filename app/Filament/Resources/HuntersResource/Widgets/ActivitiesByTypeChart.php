<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use Filament\Widgets\ChartWidget;

class ActivitiesByTypeChart extends ChartWidget
{
    public ?int $leadId = null;
    
    public function mount(?int $leadId = null): void
    {
        $this->leadId = $leadId;
    }
    
    public function getHeading(): string
    {
        return 'Activities by Type (30d)' . ($this->leadId ? " - Lead #{$this->leadId}" : '');
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $query = Activity::query()
            ->selectRaw('COALESCE(activity_type, "other") as type, COUNT(*) as count')
            ->where(function($q) {
                $q->where('date_time', '>=', now()->subDays(30))
                  ->orWhere('created_at', '>=', now()->subDays(30));
            });
        
        // Filter by lead if leadId is provided
        if ($this->leadId) {
            $query->where('lead_id', $this->leadId);
        }
        
        $rows = $query->groupBy('type')
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


