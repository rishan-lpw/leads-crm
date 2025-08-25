<?php

namespace App\Filament\Resources\CronResource\Pages;

use App\Filament\Resources\CronResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\CronStatsOverview;
use App\Models\Cron;


class ListCrons extends ListRecords
{
    protected static string $resource = CronResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CronStatsOverview::class,
        ];
    }
}
