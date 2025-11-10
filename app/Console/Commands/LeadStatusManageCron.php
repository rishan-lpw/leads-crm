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

        $cronRules = Cron::where('category', 'Other')->first();
        $rule1Days = (int) ($cronRules?->rule_1_days ?? 7);

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
         * Only when a call activity exists and its payment status is not completed
         */
        Lead::where('status', 'new')
            ->whereHas('activities', function ($query) {
                $query->where('activity_type', 'call')
                    ->where(function ($paymentQuery) {
                        $paymentQuery->whereNull('payment_status_id')
                            ->orWhere('payment_status_id', '!=', 1);
                    });
            })
            ->update(['status' => 'follow_up']);

        /**
         * Rule 3: New -> transferred
         * For non Pending Payment sources after rule_1_days
         */
        $transferCutoffDate = $now->copy()->subDays((int) $rule1Days);

        Lead::where('status', 'new')
            ->where(function ($query) {
                $query->whereNull('source')
                    ->orWhere('source', '!=', 'Pending Payment');
            })
            ->whereDate('posted_date', '<=', $transferCutoffDate)
            ->update(['status' => 'transferred']);

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

        /**vvv
         * Rule 5: to_be_expired -> expired
         * If note = NULL within 1 day
         */
        // Lead::where('status', 'to_be_expired')
        //     ->whereDate('posted_date', '<=', $now->copy()->subDay())
        //     ->whereNull('note')
        //     ->update(['status' => 'expired']);

        /**
         * Rule 6: Unassigned Leads -> un_mapped
         * For any lead that is not mapped to a user
         */
        Lead::whereNull('user_id')
            ->where('status', '!=', 'un_mapped')
            ->update(['status' => 'un_mapped']);

        $this->info("Lead status management cron executed successfully at " . $now);
    }
}
