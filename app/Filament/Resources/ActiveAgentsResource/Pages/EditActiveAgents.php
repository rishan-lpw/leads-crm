<?php

namespace App\Filament\Resources\ActiveAgentsResource\Pages;

use App\Filament\Resources\ActiveAgentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActiveAgents extends EditRecord
{
    protected static string $resource = ActiveAgentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
