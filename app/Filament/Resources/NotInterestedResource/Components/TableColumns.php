<?php

namespace App\Filament\Resources\NotInterestedResource\Components;

use App\Filament\Resources\HuntersResource\Components\TableColumns as HuntersTableColumns;

class TableColumns
{
    public static function get(): array
    {
        return HuntersTableColumns::getColumns();
    }
}


