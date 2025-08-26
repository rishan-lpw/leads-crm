<?php

namespace App\Filament\Resources\RenewResource\Pages;

use App\Filament\Resources\RenewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRenews extends ListRecords
{
    protected static string $resource = RenewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
