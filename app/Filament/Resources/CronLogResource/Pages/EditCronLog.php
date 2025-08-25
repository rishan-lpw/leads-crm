<?php

namespace App\Filament\Resources\CronLogResource\Pages;

use App\Filament\Resources\CronLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCronLog extends EditRecord
{
    protected static string $resource = CronLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
