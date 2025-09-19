<?php

namespace App\Filament\Resources\CronLogResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CronLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCronLogs extends ListRecords
{
    protected static string $resource = CronLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
