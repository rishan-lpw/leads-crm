<?php

namespace App\Filament\Resources\HuntersResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\HuntersResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        $user = Auth::user();

        // base query is used for count calculations; respect junior user scope
        $baseQuery = function () use ($user) {
            $q = Lead::query();
            if ($user && $user->user_level_id == 1) {
                $q->where('user_id', $user->id);
            }
            return $q;
        };

        $cacheTtl = 60; // seconds — small TTL to reduce DB load but keep reasonably fresh

        $countFor = function ($key, $modifier = null) use ($baseQuery, $cacheTtl) {
            $cacheKey = "hunters_count_{$key}";
            return Cache::remember($cacheKey, $cacheTtl, function () use ($baseQuery, $modifier) {
                $q = $baseQuery();
                if (is_callable($modifier)) {
                    $q = $modifier($q);
                }
                return (int) $q->count();
            });
        };

        return [
            'new' => Tab::make('New')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge($countFor('new', fn($q) => $q->where('status', 'new')))
                ->icon('heroicon-o-check-circle')
                ->badgeColor('success'),

            'transferred' => Tab::make('Transferred')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'transferred'))
                ->badge($countFor('transferred', fn($q) => $q->where('status', 'transferred')))
                ->icon('heroicon-o-arrow-path')
                ->badgeColor('gray'),

            'follow_up' => Tab::make('Follow-Up')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'follow_up'))
                ->badge($countFor('follow_up', fn($q) => $q->where('status', 'follow_up')))
                ->icon('heroicon-o-calendar')
                ->badgeColor('info'),

            'reminder' => Tab::make('Reminder')
                ->lazy()
                // If any activity has reminder_at in activity_follow_up table, those activities should be move to the reminder tab.
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('activities', function ($query) {
                    $query->whereHas('followUp', function ($query) {
                        $query->whereNotNull('reminder_at');
                    });
                }))
                ->badge($countFor('reminder', fn($q) => $q->whereHas('activities', function ($query) {
                    $query->whereHas('followUp', function ($query) {
                        $query->whereNotNull('reminder_at');
                    });
                })))
                ->icon('heroicon-o-bell-alert')
                ->badgeColor('warning'),

            'last_day' => Tab::make('Last Day')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'last_day'))
                ->badge($countFor('last_day', fn($q) => $q->where('status', 'last_day')))
                ->icon('heroicon-o-clock')
                ->badgeColor('danger'),

            // 'system' => Tab::make('System')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'system'))
            //     ->badge($countFor('system', fn($q) => $q->where('status', 'system')))
            //     ->badgeColor('info'),
            'favorite' => Tab::make('Favorite')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_favourite', 1))
                ->badge($countFor('favorite', fn($q) => $q->where('is_favourite', 1)))
                ->icon('heroicon-o-star')
                ->badgeColor('gray'),

            'un_mapped' => Tab::make('Un Mapped')
                ->lazy()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'un_mapped'))
                ->badge($countFor('un_mapped', fn($q) => $q->where('status', 'un_mapped')))
                ->icon('heroicon-o-map')
                ->badgeColor('warning'),

            'all' => Tab::make('All')
                ->lazy()
                ->default(true)
                ->badge($countFor('all'))
                ->icon('heroicon-o-list-bullet')
                ->badgeColor('primary'),

            // Add other 2 categories called 'Éxpired', 'To Be Expired'
            // 'to_be_expired' => Tab::make('To Be Expired')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'to_be_expired'))
            //     ->badge(Lead::where('status', 'to_be_expired')->count())
            //     ->badgeColor('warning'),
            // 'expired' => Tab::make('Expired')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'expired'))
            //     ->badge(Lead::where('status', 'expired')->count())
            //     ->badgeColor('danger'),
        ];
    }
}
