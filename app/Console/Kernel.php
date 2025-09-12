<?php

namespace App\Console;

use App\Console\Commands\AssignLeadsCron;
use App\Console\Commands\UpdateLeadStatuses;
use App\Console\Commands\LeadStatusManageCron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Cron;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AssignLeadsCron::class,
        UpdateLeadStatuses::class,
        LeadStatusManageCron::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('assign:leads')->dailyAt('09:00');
        // $schedule->command('update:lead-status')->dailyAt('10:00');
        $schedule->command('cron:lead-status-manage')->dailyAt('08:00');
    }

}
