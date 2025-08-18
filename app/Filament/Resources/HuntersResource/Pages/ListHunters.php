<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use App\Filament\Resources\HuntersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHunters extends ListRecords
{
    protected static string $resource = HuntersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
