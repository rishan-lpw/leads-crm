<?php

namespace App\Filament\Resources\AMChangedResource\Pages;

use App\Filament\Resources\AMChangedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAMChanged extends EditRecord
{
    protected static string $resource = AMChangedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
