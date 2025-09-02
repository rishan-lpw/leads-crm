<?php

namespace App\Filament\Resources\NotInterestedResource\Pages;

use App\Filament\Resources\NotInterestedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotInterested extends EditRecord
{
    protected static string $resource = NotInterestedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
