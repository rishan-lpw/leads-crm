<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Activity;
use App\Models\Funnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FunnelChart extends ChartWidget
{
    public ?int $leadId = null;
    
    public function mount(?int $leadId = null): void
    {
        $this->leadId = $leadId;
    }
    
    public function getHeading(): string
    {
        return 'Funnel Progress' . ($this->leadId ? " - Lead #{$this->leadId}" : '');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $query = Activity::query()
            ->select('funnel_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('funnel_id')
            ->where(function($q) {
                $q->where('date_time', '>=', now()->subDays(90))
                  ->orWhere('created_at', '>=', now()->subDays(90));
            });
        
        // Filter by lead if leadId is provided
        if ($this->leadId) {
            $query->where('lead_id', $this->leadId);
        }
        
        $rows = $query->groupBy('funnel_id')
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

