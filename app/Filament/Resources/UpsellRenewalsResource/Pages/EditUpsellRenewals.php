<?php

namespace App\Filament\Resources\UpsellRenewalsResource\Pages;

use App\Filament\Resources\UpsellRenewalsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUpsellRenewals extends EditRecord
{
    protected static string $resource = UpsellRenewalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
