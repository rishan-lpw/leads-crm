<?php

namespace App\Filament\Resources\AMChangedResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AMChangedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAMChangeds extends ListRecords
{
    protected static string $resource = AMChangedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
