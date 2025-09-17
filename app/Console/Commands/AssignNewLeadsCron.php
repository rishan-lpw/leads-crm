<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AssignNewLeadsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:assign-new-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assign Account Managers to unassigned leads (new, follow_up, system, to_be_expired, expired) using round robin method';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting unassigned lead assignment process...');

        try {
            // Get all available Account Managers
            $accountManagers = $this->getAccountManagers();

            if ($accountManagers->isEmpty()) {
                $this->warn('⚠️ No active account managers found.');
                Log::warning('AssignNewLeadsCron: No active account managers found');
                return Command::FAILURE;
            }

            // Get ALL unassigned leads (no status filter)
            $unassignedLeads = Lead::whereNull('user_id')
                ->orderBy('created_at', 'asc')
                ->get();

            if ($unassignedLeads->isEmpty()) {
                $this->info('✅ No unassigned leads found.');
                Log::info('AssignNewLeadsCron: No unassigned leads found');
                return Command::SUCCESS;
            }

            $this->info("📋 Found {$unassignedLeads->count()} unassigned leads to process.");
            $this->info("👥 Available Account Managers: {$accountManagers->count()}");
            $this->newLine();

            // Round robin logic
            $cacheKey = 'am_assignment_last_index';
            $lastIndex = Cache::get($cacheKey, -1);
            $currentIndex = ($lastIndex + 1) % $accountManagers->count();

            $assignedCount = 0;

            DB::transaction(function () use ($unassignedLeads, $accountManagers, &$currentIndex, &$assignedCount, $cacheKey) {
                foreach ($unassignedLeads as $lead) {
                    $assignedUser = $accountManagers[$currentIndex];

                    $lead->update([
                        'user_id'    => $assignedUser->id,
                        'updated_at' => now(),
                    ]);

                    $this->line("✅ Assigned Lead #{$lead->id} ({$lead->heading}) → {$assignedUser->name}");

                    $assignedCount++;

                    // Next AM
                    $currentIndex = ($currentIndex + 1) % $accountManagers->count();
                }

                // Save round robin index
                Cache::put(
                    $cacheKey,
                    ($currentIndex - 1 + $accountManagers->count()) % $accountManagers->count(),
                    now()->addDays(30)
                );
            });

            $this->newLine();
            $this->info("🎯 Successfully assigned {$assignedCount} leads to account managers.");

            Log::info("AssignNewLeadsCron: Assigned {$assignedCount} unassigned leads", [
                'assigned_count' => $assignedCount,
                'total_ams'      => $accountManagers->count(),
                'next_index'     => $currentIndex % $accountManagers->count(),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during lead assignment: ' . $e->getMessage());
            Log::error('AssignNewLeadsCron failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Show assignment summary and round robin status
     */
    private function showAssignmentSummary($accountManagers, $assignedCount)
    {
        $this->info("📊 Assignment Summary:");
        $this->info("   • Total AMs Available: {$accountManagers->count()}");
        $this->info("   • Leads Assigned: {$assignedCount}");
        
        // Show assignment breakdown by status for just assigned leads
        if ($assignedCount > 0) {
            $recentlyAssigned = Lead::whereNotNull('user_id')
                ->whereIn('status', ['new', 'follow_up', 'system', 'to_be_expired', 'expired'])
                ->where('updated_at', '>=', now()->subMinutes(5))
                ->get();
                
            if ($recentlyAssigned->isNotEmpty()) {
                $statusBreakdown = $recentlyAssigned->groupBy('status')->map->count();
                $this->info("📋 Assignment by Status (this run):");
                foreach ($statusBreakdown as $status => $count) {
                    $this->line("   • {$status}: {$count} leads assigned");
                }
            }
        }
        
        // Show current assignment counts for each AM
        $this->info("📈 Current Lead Distribution (All Leads):");
        foreach ($accountManagers as $am) {
            $leadCount = Lead::where('user_id', $am->id)->count();
            $this->line("   • {$am->name}: {$leadCount} total leads");
        }

        $nextIndex = Cache::get('am_assignment_last_index', -1);
        $nextAM = $accountManagers[($nextIndex + 1) % $accountManagers->count()];
        $this->info("🔄 Next Assignment: {$nextAM->name}");
    }

    /**
     * Reset the round robin counter (useful for testing)
     */
    public function resetRoundRobin()
    {
        Cache::forget('am_assignment_last_index');
        $this->info('🔄 Round robin counter reset.');
    }

    /**
     * Show current round robin status without assigning leads
     */
    public function showStatus()
    {
        // don't want to check the status
        $accountManagers = User::where(function($query) {
            $query->whereNotNull('name');
        })->orderBy('id')->get();

        if ($accountManagers->isEmpty()) {
            $this->warn('No active account managers found.');
            return;
        }

        $this->info("👥 Account Managers ({$accountManagers->count()}):");
        foreach ($accountManagers as $index => $am) {
            $leadCount = Lead::where('user_id', $am->id)->count();
            $this->line("   {$index}: {$am->name} ({$leadCount} leads)");
        }

        $lastIndex = Cache::get('am_assignment_last_index', -1);
        $nextIndex = ($lastIndex + 1) % $accountManagers->count();
        $nextAM = $accountManagers[$nextIndex];
        
        $this->info("🔄 Round Robin Status:");
        $this->info("   Last Assigned Index: {$lastIndex}");
        $this->info("   Next Assignment: {$nextIndex} - {$nextAM->name}");

        $unassignedCount = Lead::whereIn('status', ['new', 'follow_up', 'system', 'to_be_expired', 'expired'])
            ->whereNull('user_id')
            ->count();
        $this->info("📋 Unassigned leads (all target statuses): {$unassignedCount}");
        
        // Show breakdown by status
        $targetStatuses = ['new', 'follow_up', 'system', 'to_be_expired', 'expired'];
        $statusBreakdown = [];
        foreach ($targetStatuses as $status) {
            $count = Lead::where('status', $status)->whereNull('user_id')->count();
            if ($count > 0) {
                $statusBreakdown[$status] = $count;
            }
        }
        
        if (!empty($statusBreakdown)) {
            $this->info("📊 Breakdown by Status:");
            foreach ($statusBreakdown as $status => $count) {
                $this->line("   • {$status}: {$count} unassigned leads");
            }
        }
    }

    /**
     * Get active account managers excluding level 1 users
     */
    private function getAccountManagers()
    {
        return User::where(function($query) {
            $query->whereNotNull('name')
                  ->where('active', true)
                  // Only assign to users who can handle leads (not level 1 users)
                  ->where('user_level_id', '!=', 1); // Exclude level 1 users from automatic assignment
        })
        ->orderBy('id')
        ->get();
    }
}
