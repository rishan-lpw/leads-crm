<?php

namespace App\Filament\Resources\CronResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CronResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrons extends ListRecords
{
    protected static string $resource = CronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
