<?php

namespace App\Filament\Resources\ActiveAgentsResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ActiveAgentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActiveAgents extends ListRecords
{
    protected static string $resource = ActiveAgentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
