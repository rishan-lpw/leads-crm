<?php

namespace App\Filament\Resources\RenewResource\Components;

use Filament\Schemas\Components\Section;

class FormSections
{
    public static function get(): array
    {
        return [
            // Minimal form sections for Renew resource - extend as needed.
            Section::make('Main')->schema([]),
        ];
    }
}