<?php

namespace App\Filament\Resources\PasswordResetResource\Pages;

use App\Filament\Resources\PasswordResetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPasswordResets extends ListRecords
{
    protected static string $resource = PasswordResetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
