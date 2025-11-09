<?php

namespace App\Filament\Resources\AllHuntersResource\Pages;

use App\Filament\Resources\AllHuntersResource;
use App\Models\Lead;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ListAllHunters extends ListRecords
{
    protected static string $resource = AllHuntersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $baseQuery = function () {
            return Lead::query()->whereIn('status', ['system', 'not_interested', 'new', 'follow_up']);
        };

        $cacheTtl = 60;

        $countFor = function (string $key, ?callable $modifier = null) use ($baseQuery, $cacheTtl) {
            $cacheKey = "all_hunters_count_{$key}";

            return Cache::remember($cacheKey, $cacheTtl, function () use ($baseQuery, $modifier) {
                $query = $baseQuery();

                if (is_callable($modifier)) {
                    $query = $modifier($query);
                }

                return (int) $query->count();
            });
        };

        return [
            'new' => Tab::make('New')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge($countFor('new', fn ($query) => $query->where('status', 'new')))
                ->icon('heroicon-o-check-circle')
                ->badgeColor('success'),

            'follow_up' => Tab::make('Follow-Up')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'follow_up'))
                ->badge($countFor('follow_up', fn ($query) => $query->where('status', 'follow_up')))
                ->icon('heroicon-o-calendar')
                ->badgeColor('info'),

            'system' => Tab::make('System')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'system'))
                ->badge($countFor('system', fn ($query) => $query->where('status', 'system')))
                ->icon('heroicon-o-cog-6-tooth')
                ->badgeColor('info'),

            'not_interested' => Tab::make('Not Interested')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'not_interested'))
                ->badge($countFor('not_interested', fn ($query) => $query->where('status', 'not_interested')))
                ->icon('heroicon-o-face-frown')
                ->badgeColor('danger'),

            'favorite' => Tab::make('Favorite')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_favourite', 1))
                ->badge($countFor('favorite', fn ($query) => $query->where('is_favourite', 1)))
                ->icon('heroicon-o-star')
                ->badgeColor('warning'),

            'all' => Tab::make('All')
                ->lazy()
                ->default(true)
                ->badge($countFor('all'))
                ->icon('heroicon-o-list-bullet')
                ->badgeColor('primary'),
        ];
    }
}


