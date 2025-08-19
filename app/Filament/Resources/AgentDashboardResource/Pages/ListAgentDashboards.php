<?php

namespace App\Filament\Resources\AgentDashboardResource\Pages;

use App\Filament\Resources\AgentDashboardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Resources\AgentDashboardResource\Widgets\AgentActivitiesStats;

class ListAgentDashboards extends ListRecords
{
    protected static string $resource = AgentDashboardResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AgentActivitiesStats::class,
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
