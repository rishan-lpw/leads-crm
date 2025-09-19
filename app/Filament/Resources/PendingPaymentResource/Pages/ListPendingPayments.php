<?php

namespace App\Filament\Resources\PendingPaymentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PendingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingPayments extends ListRecords
{
    protected static string $resource = PendingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
