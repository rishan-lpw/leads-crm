<?php

namespace App\Filament\Resources\AgentDashboardResource\Widgets;

use App\Models\Activity;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends ChartWidget
{
    protected ?string $heading = 'Chart';
    protected ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        return [
            
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getStats(): array
    {
        $now = now();

        $totalActivities = Activity::count();

        $thisWeekStart = Carbon::now()->subDays(7);
        $prevWeekStart = Carbon::now()->subDays(14);

        $thisWeek = Activity::where('created_at', '>=', $thisWeekStart)->count();
        $prevWeek = Activity::whereBetween('created_at', [$prevWeekStart, $thisWeekStart])->count();

        $percentChange = $prevWeek > 0
            ? round((($thisWeek - $prevWeek) / $prevWeek) * 100, 1)
            : 0;

        $increaseIcon = $percentChange >= 0
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';

        $activeAgents30d = Activity::where('created_at', '>=', Carbon::now()->subDays(30))
            ->distinct('user_id')
            ->count('user_id');

        $completed = Activity::where('status', 'completed')->count();
        $withStatus = Activity::whereNotNull('status')->count();
        $completionRate = $withStatus > 0 ? round(($completed / $withStatus) * 100, 1) : 0;

        return [
            Stat::make('Total Activities', number_format($totalActivities))
                ->description('All time')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Activities (7d)', number_format($thisWeek))
                ->description($percentChange . '% ' . ($percentChange >= 0 ? 'increase' : 'decrease'))
                ->descriptionIcon($increaseIcon)
                ->color($percentChange >= 0 ? 'success' : 'danger'),

            Stat::make('Active Agents (30d)', number_format($activeAgents30d))
                ->description('Unique agents with activity')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Completion Rate', $completionRate . '%')
                ->description('Based on status')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($completionRate >= 50 ? 'success' : 'warning'),
        ];
    }
}
