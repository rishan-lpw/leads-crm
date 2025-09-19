<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Lead;
use App\Models\User;
use App\Models\Cron;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AssignLeadsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:auto-assign {cronId?}';
    protected $description = 'Automatically assign leads for upsell and pending payment cases based on cron rules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        $cronId = $this->argument('cronId');

        $this->info("🚀 Starting automatic lead assignment process...");
        $this->newLine();

        // Get cron rules
        if ($cronId) {
            $crons = Cron::where('id', $cronId)->get();
            if ($crons->isEmpty()) {
                $this->error("Cron rule with ID {$cronId} not found.");
                return 1;
            }
        } else {
            $crons = Cron::all();
        }

        if ($crons->isEmpty()) {
            $this->warn('No cron rules found.');
            return 0;
        }

        $totalAssigned = 0;
        $totalProcessed = 0;

        foreach ($crons as $cronRule) {
            $this->info("📋 Processing Cron Rule: {$cronRule->id} - {$cronRule->name} (Category: {$cronRule->category})");
            $this->info("   Rule 1 Days: {$cronRule->rule_1_days} | Rule 2 Days: {$cronRule->rule_2_days}");
            $this->info("   Assigned Member: {$cronRule->member}");
            $this->newLine();

            // Process based on category
            switch (strtolower($cronRule->category)) {
                case 'pending payment':
                    $result = $this->processPendingPayments($cronRule, $today);
                    break;
                
                case 'ikman':
                case 'facebook-ads':
                default:
                    $result = $this->processUpsellLeads($cronRule, $today);
                    break;
            }

            $totalAssigned += $result['assigned'];
            $totalProcessed += $result['processed'];

            $this->newLine();
        }

        // ---- SUMMARY ----
        $this->info("🎯 FINAL SUMMARY:");
        $this->info("   Total Leads Processed: {$totalProcessed}");
        $this->info("   Total Leads Assigned: {$totalAssigned}");
        $this->info("✅ Assignment process completed successfully!");

        return 0;
    }

    /**
     * Process upsell leads (Ikman, Facebook-Ads, etc.)
     */
    private function processUpsellLeads($cronRule, $today)
    {
        $this->info("📈 Processing Upsell Leads (Status: upsell, Days >= {$cronRule->rule_1_days})");
        
        $upsellLeads = Lead::where('status', 'upsell')
            ->whereNotNull('posted_date')
            ->get();

        $processed = 0;
        $assigned = 0;

        foreach ($upsellLeads as $lead) {
            $processed++;
            $days = Carbon::parse($lead->posted_date)->diffInDays($today);

            // Use rule_1_days from cron table for upsell assignment
            if ($days >= $cronRule->rule_1_days) {
                if ($lead->assigned_sh) {
                    $this->line("  → Lead {$lead->id} already assigned to SH {$lead->assigned_sh}");
                    continue;
                }

                $shId = $this->getNextAssignee('senior');
                
                if ($shId) {
                    $lead->update([
                        'assigned_sh' => $shId,
                        'am' => $cronRule->member, // Assign to the member from cron rule
                        'last_update_by' => 'System',
                        'last_update_date' => $today,
                        'latest_comments' => ($lead->latest_comments ? $lead->latest_comments . "\n" : '') . 
                                           "[" . now()->format('Y-m-d H:i:s') . "] System: Assigned to SH {$shId} ({$cronRule->member}) for upsell (after {$days} days, Rule: {$cronRule->rule_1_days} days)"
                    ]);

                    $this->info("  ✅ Lead {$lead->id} → Assigned to SH {$shId} ({$cronRule->member}) after {$days} days");
                    $assigned++;
                } else {
                    $this->warn("  ⚠️ No available Senior Hunters found for assignment");
                }
            } else {
                $this->line("  → Lead {$lead->id} waiting ({$days}/{$cronRule->rule_1_days} days)");
            }
        }

        $this->info("📊 Upsell Summary for Rule {$cronRule->id}: {$assigned}/{$processed} leads assigned");
        
        return ['assigned' => $assigned, 'processed' => $processed];
    }

    /**
     * Process pending payment leads
     */
    private function processPendingPayments($cronRule, $today)
    {
        $this->info("💳 Processing Pending Payment Leads (Status: pending_payment, Days >= {$cronRule->rule_2_days})");
        
        $pendingLeads = Lead::where('status', 'pending_payment')
            ->whereNotNull('posted_date')
            ->get();

        $processed = 0;
        $assigned = 0;

        foreach ($pendingLeads as $lead) {
            $processed++;
            $days = Carbon::parse($lead->posted_date)->diffInDays($today);

            // Use rule_2_days from cron table for pending payment assignment
            if ($days >= $cronRule->rule_2_days) {
                if ($lead->assigned_jh) {
                    $this->line("  → Lead {$lead->id} already assigned to JH {$lead->assigned_jh}");
                    continue;
                }

                $jhId = $this->getNextAssignee('junior');

                if ($jhId) {
                    // Update the lead table by changing user_id to the new JH ID
                    $lead->update([
                        'user_id' => $jhId,
                        'assigned_jh' => $jhId,
                        'am' => $cronRule->member, // Assign to the member from cron rule
                        'last_update_by' => 'System',
                        'last_update_date' => $today,
                        'latest_comments' => ($lead->latest_comments ? $lead->latest_comments . "\n" : '') . 
                                           "[" . now()->format('Y-m-d H:i:s') . "] System: Assigned to JH {$jhId} ({$cronRule->member}) for pending payment (after {$days} days, Rule: {$cronRule->rule_2_days} days)"
                    ]);

                    $this->info("  ✅ Lead {$lead->id} → Assigned to JH {$jhId} ({$cronRule->member}) after {$days} days");
                    $assigned++;
                } else {
                    $this->warn("  ⚠️ No available Junior Hunters found for assignment");
                }
            } else {
                $this->line("  → Lead {$lead->id} waiting ({$days}/{$cronRule->rule_2_days} days)");
            }
        }

        $this->info("📊 Pending Payment Summary for Rule {$cronRule->id}: {$assigned}/{$processed} leads assigned");
        
        return ['assigned' => $assigned, 'processed' => $processed];
    }

    /**
     * Get the next user ID in round-robin order
     *
     * @param string $type ('junior' or 'senior')
     * @return int|null
     */
    private function getNextAssignee($type)
    {
        try {
            // Define user type IDs
            $userTypeMapping = [
                'junior' => 1,  // Junior Hunter
                'senior' => 2,  // Senior Hunter
            ];

            if (!isset($userTypeMapping[$type])) {
                $this->error("Invalid user type: {$type}");
                return null;
            }

            $userTypeId = $userTypeMapping[$type];

            // Get all active users of the specified type
            $users = User::where('user_type_id', $userTypeId)
                ->where('status', 'active') // Assuming you have an active status
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            if (empty($users)) {
                // If no users with status 'active', try without status filter
                $users = User::where('user_type_id', $userTypeId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->toArray();
                
                if (empty($users)) {
                    $this->warn("No users found for type: {$type} (user_type_id: {$userTypeId})");
                    return null;
                }
            }

            // Cache key for round-robin tracking
            $cacheKey = "round_robin_{$type}_assignee";

            // Get the last assigned user index from cache
            $lastIndex = Cache::get($cacheKey, -1);

            // Calculate next index (round-robin)
            $nextIndex = ($lastIndex + 1) % count($users);

            // Get the next user ID
            $nextUserId = $users[$nextIndex];

            // Store the current index in cache for next time
            // Cache for 24 hours to ensure persistence across multiple runs
            Cache::put($cacheKey, $nextIndex, now()->addHours(24));

            $this->line("  🔄 Round-robin assignment: Index {$nextIndex}/{" . (count($users) - 1) . "} → User ID {$nextUserId}");

            return $nextUserId;

        } catch (Exception $e) {
            $this->error("Error in getNextAssignee: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reset round-robin counters (useful for testing or manual reset)
     *
     * @param string|null $type
     */
    public function resetRoundRobin($type = null)
    {
        if ($type) {
            Cache::forget("round_robin_{$type}_assignee");
            $this->info("Reset round-robin counter for: {$type}");
        } else {
            Cache::forget("round_robin_junior_assignee");
            Cache::forget("round_robin_senior_assignee");
            $this->info("Reset all round-robin counters");
        }
    }

    /**
     * Show current round-robin status
     */
    public function showRoundRobinStatus()
    {
        $juniorIndex = Cache::get("round_robin_junior_assignee", "Not set");
        $seniorIndex = Cache::get("round_robin_senior_assignee", "Not set");

        $this->info("🔄 Current Round-Robin Status:");
        $this->info("   Junior Hunters: Index {$juniorIndex}");
        $this->info("   Senior Hunters: Index {$seniorIndex}");

        // Show available users
        $juniorUsers = User::where('user_type_id', 1)->count();
        $seniorUsers = User::where('user_type_id', 2)->count();

        $this->info("📊 Available Users:");
        $this->info("   Junior Hunters: {$juniorUsers} users");
        $this->info("   Senior Hunters: {$seniorUsers} users");
    }
}
