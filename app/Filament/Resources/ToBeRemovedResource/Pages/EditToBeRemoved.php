<?php

namespace App\Filament\Resources\ToBeRemovedResource\Pages;

use App\Filament\Resources\ToBeRemovedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToBeRemoved extends EditRecord
{
    protected static string $resource = ToBeRemovedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
