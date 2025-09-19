<?php

namespace App\Filament\Resources\AddOnResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAddOns extends ListRecords
{
    protected static string $resource = AddOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
