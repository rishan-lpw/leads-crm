<?php

namespace App\Filament\Resources\PendingPaymentResource\Pages;

use App\Filament\Resources\PendingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePendingPayment extends CreateRecord
{
    protected static string $resource = PendingPaymentResource::class;
}
