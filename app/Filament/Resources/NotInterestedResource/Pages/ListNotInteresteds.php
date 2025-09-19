<?php

namespace App\Filament\Resources\NotInterestedResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\NotInterestedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotInteresteds extends ListRecords
{
    protected static string $resource = NotInterestedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
