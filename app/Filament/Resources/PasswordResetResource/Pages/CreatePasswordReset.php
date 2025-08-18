<?php

namespace App\Filament\Resources\PasswordResetResource\Pages;

use App\Filament\Resources\PasswordResetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePasswordReset extends CreateRecord
{
    protected static string $resource = PasswordResetResource::class;
}
