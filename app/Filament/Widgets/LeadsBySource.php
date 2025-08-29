<?php

namespace App\Filament\Resources\HuntersResource\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsBySource extends ChartWidget
{
    protected static ?string $heading = 'Leads by Source';

    protected function getData(): array
    {
        $data = Lead::selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        return [
            'datasets' => [
                [
                    'label' => 'Leads by Source',
                    'data' => array_values($data->toArray()),
                ],
            ],
            'labels' => array_keys($data->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
