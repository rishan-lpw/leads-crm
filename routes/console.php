<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Models\Cron;
use App\Models\CronLog;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    $crons = Cron::where('is_active', true)->get();

    foreach ($crons as $cron) {
        Schedule::command($cron->command)
            ->cron($cron->frequency)
            ->before(function () use ($cron, &$logId) {
                $log = CronLog::create([
                    'cron_id'    => $cron->id,
                    'started_at' => now(),
                ]);
                $cron->update(['last_run_at' => now()]);
                $logId = $log->id;
            })
            ->onSuccess(function () use (&$logId) {
                CronLog::find($logId)?->update([
                    'finished_at' => now(),
                    'status'      => true,
                    'output'      => 'Completed successfully',
                ]);
            })
            ->onFailure(function () use (&$logId) {
                CronLog::find($logId)?->update([
                    'finished_at' => now(),
                    'status'      => false,
                    'output'      => 'Job failed',
                ]);
            });
    }
})->everyMinute(); // this ensures DB crons are checked each minute
