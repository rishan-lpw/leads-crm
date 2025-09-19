<?php

namespace App\Filament\Resources\UpsellResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\UpsellResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUpsells extends ListRecords
{
    protected static string $resource = UpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
