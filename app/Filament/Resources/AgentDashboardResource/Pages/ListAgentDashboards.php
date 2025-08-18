<?php

namespace App\Filament\Resources\AgentDashboardResource\Pages;

use App\Filament\Resources\AgentDashboardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgentDashboards extends ListRecords
{
    protected static string $resource = AgentDashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
