<?php

namespace App\Console;

use App\Console\Commands\AssignLeadsCron;
use App\Console\Commands\AssignNewLeadsCron;
use App\Console\Commands\UpdateLeadStatuses;
use App\Console\Commands\LeadStatusManageCron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Cron;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AssignLeadsCron::class,
        AssignNewLeadsCron::class,
        UpdateLeadStatuses::class,
        LeadStatusManageCron::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('assign:leads')->dailyAt('09:00');
        // $schedule->command('update:lead-status-every-minute')->everyMinute();
        $schedule->command('cron:lead-status-manage')->everyMinute();

        // Auto-assign new leads to Account Managers every day at midnight
        $schedule->command('cron:assign-new-leads')->everyMinute();

        $schedule->call(function () {
            Log::info('✅ Test cron executed at ' . now());
        })->everyMinute();
    }
}
