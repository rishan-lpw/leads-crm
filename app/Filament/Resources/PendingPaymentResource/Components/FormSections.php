<?php

namespace App\Filament\Resources\PendingPaymentResource\Components;

use Filament\Schemas\Components\Section;

class FormSections
{
    public static function get(): array
    {
        return [
            // keep empty or add form sections if pending payment create/edit is required
            Section::make('Main')->schema([]),
        ];
    }
}