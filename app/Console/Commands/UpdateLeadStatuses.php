<?php

namespace App\Console\Commands;

use App\Models\Cron;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateLeadStatuses extends Command
{
    protected $signature = 'leads:update-status {cronId?}';
    protected $description = 'Update lead statuses based on cron rules';

    public function handle()
    {
        $cronId = $this->argument('cronId');

        // Get cron rules
        if ($cronId) {
            $crons = Cron::where('id', $cronId)->get();
            if ($crons->isEmpty()) {
                $this->error("❌ Cron rule with ID {$cronId} not found.");
                return 1;
            }
        } else {
            $crons = Cron::all();
        }

        if ($crons->isEmpty()) {
            $this->warn('⚠️ No cron rules found.');
            return 0;
        }

        $totalProcessed = 0;
        $totalUpdated   = 0;

        foreach ($crons as $rule) {
            // Safely convert member/category to string
            $member   = is_array($rule->member) ? implode(', ', $rule->member) : (string) $rule->member;
            $category = is_array($rule->category) ? implode(', ', $rule->category) : (string) $rule->category;

            $this->info("🔎 Processing rule: {$rule->id} for member: {$member} (Category: {$category})");

            // Query leads by AM (account manager)
            $leads = Lead::where('am', $member)
                ->whereNotNull('posted_date')
                ->get();

            if ($leads->isEmpty()) {
                $this->warn("⚠️ No leads found for member: {$member}");
                continue;
            }

            $this->info("📌 Found {$leads->count()} leads for member: {$member}");
            $ruleUpdated = 0;

            foreach ($leads as $lead) {
                $totalProcessed++;

                if (!$lead->posted_date) {
                    $this->warn("⏩ Lead ID {$lead->id} has no posted_date, skipping...");
                    continue;
                }

                $daysSince      = Carbon::parse($lead->posted_date)->diffInDays(now());
                $originalStatus = $lead->status;
                $updated        = false;

                // Rule 2: Move to "system"
                if (
                    $daysSince >= $rule->rule_2_days &&
                    !in_array($lead->status, ['system', 'closed', 'converted'])
                ) {
                    $lead->update([
                        'status'           => 'system',
                        'last_update_by'   => 'System',
                        'last_update_date' => now(),
                        'latest_comments'  => trim(($lead->latest_comments ? $lead->latest_comments . "\n\n" : '') .
                            '[' . now()->format('Y-m-d H:i:s') . '] System: Auto-moved to system status after ' . $daysSince . ' days (Rule 2)'),
                    ]);
                    $updated = true;
                    $this->line("  ✅ Lead ID {$lead->id}: {$originalStatus} → system (after {$daysSince} days)");
                }
                // Rule 1: Move to "follow_up"
                elseif (
                    $daysSince >= $rule->rule_1_days &&
                    !in_array($lead->status, ['follow_up', 'system', 'closed', 'converted'])
                ) {
                    $lead->update([
                        'status'           => 'follow_up',
                        'last_update_by'   => 'System',
                        'last_update_date' => now(),
                        'latest_comments'  => trim(($lead->latest_comments ? $lead->latest_comments . "\n\n" : '') .
                            '[' . now()->format('Y-m-d H:i:s') . '] System: Auto-moved to follow_up status after ' . $daysSince . ' days (Rule 1)'),
                    ]);
                    $updated = true;
                    $this->line("  ✅ Lead ID {$lead->id}: {$originalStatus} → follow_up (after {$daysSince} days)");
                }

                if ($updated) {
                    $ruleUpdated++;
                    $totalUpdated++;
                }
            }

            $this->info("📊 Rule {$rule->id} completed: {$ruleUpdated} leads updated\n");
        }

        $this->info("🎯 Process completed!");
        $this->info("➡️ Total leads processed: {$totalProcessed}");
        $this->info("🔄 Total leads updated: {$totalUpdated}");

        return 0;
    }
}
