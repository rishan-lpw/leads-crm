<?php

namespace App\Console\Commands;

use App\Models\Cron;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateLeadStatuses extends Command
{
    protected $signature = 'update:lead-status';

    protected $description = 'Automatically update lead statuses based on time and conditions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('UpdateLeadStatus started...');

        // Example 1: If payment received → after 3 days assign to SH for upsell
        $upsellLeads = Lead::where('status', 'upsell_pending')
            ->whereDate('payment_date', '<=', Carbon::now()->subDays(3))
            ->get();

        foreach ($upsellLeads as $lead) {
            $lead->status = 'upsell_ready';
            $lead->save();
            $this->line("Lead ID {$lead->id} moved to Upsell Ready");
        }

        // Example 2: If pending payment → after 14 days reassign
        $pendingLeads = Lead::where('status', 'pending_payment')
            ->whereDate('created_at', '<=', Carbon::now()->subDays(14))
            ->get();

        foreach ($pendingLeads as $lead) {
            $lead->status = 'reassign';
            $lead->assigned_to = null;
            $lead->save();
            $this->line("Lead ID {$lead->id} marked for reassignment");
        }

        $this->info('UpdateLeadStatus completed successfully.');
    }
}
