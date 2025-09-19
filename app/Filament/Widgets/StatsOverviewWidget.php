<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // Get total number of customers
        $totalCustomers = Customer::count();
        
        // Get customers created in the last 30 days
        $lastMonth = Carbon::now()->subDays(30);
        $newCustomers = Customer::where('created_at', '>=', $lastMonth)->count();
        
        // Get total number of activities
        $totalActivities = Activity::count();
        
        // Get activities created in the last 7 days
        $lastWeek = Carbon::now()->subDays(7);
        $recentActivities = Activity::where('created_at', '>=', $lastWeek)->count();
        
        // Get total users
        $totalUsers = User::count();
        
        return [
            Stat::make('Total Customers', $totalCustomers)
                ->description('Overall customer count')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([7, 4, 6, 8, 5, 6, 8, 12, 15, $totalCustomers]),
                
            Stat::make('New Customers (30d)', $newCustomers)
                ->description('Added in last 30 days')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
                
            Stat::make('Total Activities', $totalActivities)
                ->description('Recent: ' . $recentActivities . ' in 7 days')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),
                
            Stat::make('Users', $totalUsers)
                ->description('System users')
                ->descriptionIcon('heroicon-m-identification')
                ->color('danger'),
        ];
    }
}
