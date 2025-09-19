<?php

namespace App\Filament\Resources\UserManageResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\UserManageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserManages extends ListRecords
{
    protected static string $resource = UserManageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
