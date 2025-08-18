<?php

namespace App\Filament\Resources\AccountReportResource\Pages;

use App\Filament\Resources\AccountReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountReports extends ListRecords
{
    protected static string $resource = AccountReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
