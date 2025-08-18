<?php

namespace App\Filament\Resources\AgentDashboardResource\Pages;

use App\Filament\Resources\AgentDashboardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgentDashboard extends EditRecord
{
    protected static string $resource = AgentDashboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
