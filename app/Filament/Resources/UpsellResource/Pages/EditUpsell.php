<?php

namespace App\Filament\Resources\UpsellResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\UpsellResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUpsell extends EditRecord
{
    protected static string $resource = UpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
