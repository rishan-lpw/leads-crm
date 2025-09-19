<?php

namespace App\Filament\Resources\CustomerMergeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CustomerMergeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerMerge extends EditRecord
{
    protected static string $resource = CustomerMergeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
