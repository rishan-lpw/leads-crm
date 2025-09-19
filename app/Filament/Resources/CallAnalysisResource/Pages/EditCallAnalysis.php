<?php

namespace App\Filament\Resources\CallAnalysisResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\CallAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCallAnalysis extends EditRecord
{
    protected static string $resource = CallAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
