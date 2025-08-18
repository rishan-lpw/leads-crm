<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use App\Filament\Resources\HuntersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHunters extends EditRecord
{
    protected static string $resource = HuntersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
