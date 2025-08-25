<?php

namespace App\Filament\Resources\CronResource\Pages;

use App\Filament\Resources\CronResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCron extends EditRecord
{
    protected static string $resource = CronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
