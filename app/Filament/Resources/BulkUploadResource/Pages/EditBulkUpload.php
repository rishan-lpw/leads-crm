<?php

namespace App\Filament\Resources\BulkUploadResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\BulkUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBulkUpload extends EditRecord
{
    protected static string $resource = BulkUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
