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

        // Fetch active cron rules
        $cronRules = Cron::where('is_active', true)->first();
        if (!$cronRules) {
            $this->info("No active cron rules found.");
            return;
        }

        $rule1Days = $cronRules->rule_1_days ?? 7; 
        $rule2Days = $cronRules->rule_2_days ?? 14;

        /**
         * Rule 1: New -> upsell
         * If activity.notes = 'payment_completed' within 3 days from assigned into new
         */
        Lead::where('status', 'new')
            ->whereDate('posted_date', '>=', $now->copy()->subDays(3))
            ->whereHas('activities', function ($query) {
                $query->where('notes', 'payment_completed');
            })
            ->update(['status' => 'upsell']);

        /**
         * Rule 2: New -> follow_up
         * If activity.notes != 'payment_completed' within rule_1_days
         */
        Lead::where('status', 'new')
            ->whereDate('posted_date', '<=', $now->copy()->subDays($rule1Days))
            ->whereDoesntHave('activities', function ($query) {
                $query->where('notes', 'payment_completed');
            })
            ->update(['status' => 'follow_up']);

        /**
         * Rule 3: follow_up -> system
         * If activity.notes != 'payment_completed' within rule_2_days
         */
        Lead::where('status', 'follow_up')
            ->whereDate('posted_date', '<=', $now->copy()->subDays($rule2Days))
            ->whereDoesntHave('activities', function ($query) {
                $query->where('notes', 'payment_completed');
            })
            ->update(['status' => 'system']);

        /**
         * Rule 4: system -> to_be_expired
         * If activity.notes != 'payment_completed' within 29 days
         */
        Lead::where('status', 'system')
            ->whereDate('posted_date', '<=', $now->copy()->subDays(29))
            ->whereDoesntHave('activities', function ($query) {
                $query->where('notes', 'payment_completed');
            })
            ->update(['status' => 'to_be_expired']);

        /**
         * Rule 5: to_be_expired -> expired
         * If activity.notes != 'payment_completed' within 1 day
         */
        Lead::where('status', 'to_be_expired')
            ->whereDate('posted_date', '<=', $now->copy()->subDay())
            ->whereDoesntHave('activities', function ($query) {
                $query->where('notes', 'payment_completed');
            })
            ->update(['status' => 'expired']);

        $this->info("Lead status management cron executed successfully at " . $now);
    }
}
