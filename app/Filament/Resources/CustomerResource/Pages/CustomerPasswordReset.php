<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\PasswordReset;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class CustomerPasswordReset extends EditRecord
{
    protected static string $resource = CustomerResource::class;
    protected static ?string $navigationLabel = 'Reset Password';
    protected static ?string $navigationGroup = 'Customers';
    protected static ?string $title = 'Customer Password Reset';

}
