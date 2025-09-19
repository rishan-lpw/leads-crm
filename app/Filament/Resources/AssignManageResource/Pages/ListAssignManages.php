<?php

namespace App\Filament\Resources\AssignManageResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AssignManageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssignManages extends ListRecords
{
    protected static string $resource = AssignManageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
