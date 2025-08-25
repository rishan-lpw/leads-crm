<?php

namespace App\Filament\Resources\CronLogResource\Pages;

use App\Filament\Resources\CronLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCronLogs extends ListRecords
{
    protected static string $resource = CronLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
