<?php

namespace App\Filament\Resources\AccountReportResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AccountReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountReports extends ListRecords
{
    protected static string $resource = AccountReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
