<?php

namespace App\Filament\Resources\AssignManageResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AssignManageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignManage extends EditRecord
{
    protected static string $resource = AssignManageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
