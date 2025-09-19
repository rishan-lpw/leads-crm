<?php

namespace App\Filament\Resources\UserManageResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\UserManageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserManage extends EditRecord
{
    protected static string $resource = UserManageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
