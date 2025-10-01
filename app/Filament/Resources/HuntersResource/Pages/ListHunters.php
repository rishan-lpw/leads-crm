<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\HuntersResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListHunters extends ListRecords
{
    protected static string $resource = HuntersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'new' => Tab::make('New')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge(Lead::where('status', 'new')->count())
                ->badgeColor('success'),
            'follow_up' => Tab::make('Follow-Up')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'follow_up'))
                ->badge(Lead::where('status', 'follow_up')->count())
                ->badgeColor('primary'),
            'reminder' => Tab::make('Reminder')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'reminder'))
                ->badge(Lead::where('status', 'reminder')->count())
                ->badgeColor('warning'),
            'last_day' => Tab::make('Last Day')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'last_day'))
                ->badge(Lead::where('status', 'last_day')->count())
                ->badgeColor('danger'),
            'system' => Tab::make('System')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'system'))
                ->badge(Lead::where('status', 'system')->count())
                ->badgeColor('info'),
            'favorite' => Tab::make('Favorite')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_favourite', 1))
                ->badge(Lead::where('is_favourite', 1)->count())
                ->badgeColor('secondary'),

            // Add other 2 categories called 'Éxpired', 'To Be Expired'
            // 'to_be_expired' => Tab::make('To Be Expired')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'to_be_expired'))
            //     ->badge(Lead::where('status', 'to_be_expired')->count())
            //     ->badgeColor('warning'),
            // 'expired' => Tab::make('Expired')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired'))
            //     ->badge(Lead::where('status', 'expired')->count())
            //     ->badgeColor('danger'),
            'all' => Tab::make('All')
                ->badge(Lead::count())
                ->badgeColor('primary'),
        ];
    }
}
