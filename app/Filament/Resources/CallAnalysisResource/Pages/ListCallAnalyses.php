<?php

namespace App\Filament\Resources\CallAnalysisResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CallAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallAnalyses extends ListRecords
{
    protected static string $resource = CallAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
