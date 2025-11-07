<?php

namespace App\Console\Commands;

use App\Models\Cron;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LeadStatusManageCron extends Command
{
    protected $signature = 'cron:lead-status-manage';
    protected $description = 'Automatically update lead statuses based on rules and activities';

    public function handle()
    {
        $now = Carbon::now();

        $rule1Days = $cronRules->rule_1_days ?? 7; 
        $rule2Days = $cronRules->rule_2_days ?? 14;

        /**
         * Rule 1: New -> upsell
         * If note = 'payment_completed' within 3 days from assigned into new
         */
        Lead::where('status', 'new')
            ->whereDate('posted_date', '>=', $now->copy()->subDays(3))
            ->where('note', 'payment_completed')        
            ->update(['status' => 'upsell']);

        /**
         * Rule 2: New -> follow_up
         * If note = NULL within rule_1_days
         */
        Lead::where('status', 'new')
            // When an activity has come, status should be follow_up
            ->whereHas('activities')
            ->update(['status' => 'follow_up']);

        // any status->Reminder, If activity_follow_up.reminder_at is not null
        Lead::whereHas('activities.followUp', function($query) {
            $query->whereNotNull('reminder_at');
        })
        ->update(['status' => 'reminder']);

        /**
         * Rule 4: system -> to_be_expired
         * If note = NULL within 29 days
         */
        Lead::where('status', 'upsell')
            ->whereDate('posted_date', '<=', $now->copy()->subDays(29))
            ->whereNull('note')
            ->update(['status' => 'to_be_expired']);

        /**
         * Rule 5: to_be_expired -> expired
         * If note = NULL within 1 day
         */
        Lead::where('status', 'to_be_expired')
            ->whereDate('posted_date', '<=', $now->copy()->subDay())
            ->whereNull('note')
            ->update(['status' => 'expired']);

        $this->info("Lead status management cron executed successfully at " . $now);
    }
}
