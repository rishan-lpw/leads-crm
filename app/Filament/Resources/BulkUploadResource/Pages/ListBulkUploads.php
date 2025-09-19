<?php

namespace App\Filament\Resources\BulkUploadResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\BulkUploadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBulkUploads extends ListRecords
{
    protected static string $resource = BulkUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
