<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected function getStats(): array
    {
        // Get total number of customers
        $totalCustomers = Customer::count();
        // $totalRevenue = Customer::sum('revenue');

        // Get customers created in the last 30 days
        $lastMonth = Carbon::now()->subDays(30);
        $newCustomers = Customer::where('created_at', '>=', $lastMonth)->count();
        
        // Calculate percent change in customers
        $previousMonthCustomers = Customer::where('created_at', '<', $lastMonth)
            ->where('created_at', '>=', Carbon::now()->subDays(60))
            ->count();
            
        $percentChange = $previousMonthCustomers > 0 
            ? round((($newCustomers - $previousMonthCustomers) / $previousMonthCustomers) * 100, 1)
            : 0;
            
        $increaseIcon = $percentChange >= 0 
            ? 'heroicon-m-arrow-trending-up' 
            : 'heroicon-m-arrow-trending-down';
        
        return [
            Stat::make('Total Customers', $totalCustomers)
                ->description('All time customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('New Customers', $newCustomers)
                ->description($percentChange . '% ' . ($percentChange >= 0 ? 'increase' : 'decrease'))
                ->descriptionIcon($increaseIcon)
                ->color($percentChange >= 0 ? 'success' : 'danger'),
                
            Stat::make('Customer Acquisition', number_format($totalCustomers / max(1, Carbon::now()->diffInMonths(Customer::min('created_at') ?? Carbon::now())), 1) . '/month')
                ->description('Average per month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

        ];
    }
}
