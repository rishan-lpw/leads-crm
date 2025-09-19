<?php

namespace App\Filament\Resources\CustomerMergeResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CustomerMergeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerMerges extends ListRecords
{
    protected static string $resource = CustomerMergeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
