<?php

namespace App\Filament\Resources\PendingPaymentResource\Pages;

use App\Filament\Resources\PendingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingPayment extends EditRecord
{
    protected static string $resource = PendingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
