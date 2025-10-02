<?php

namespace App\Filament\Resources\UpsellResource\Components;

use Filament\Schemas\Components\Section;

class FormSections
{
    public static function get(): array
    {
        return [
            // keep minimal; extend per your Upsell create/edit form needs
            Section::make('Main')->schema([]),
        ];
    }
}