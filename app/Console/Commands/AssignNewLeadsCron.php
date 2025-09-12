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
        $this->info('🚀 Starting new lead assignment process...');

        try {
            // Get all available Account Managers (users who can be assigned to leads)
            // Assuming Account Managers have a specific user_type_id or role
            $accountManagers = User::where(function($query) {
                // Add your criteria for Account Managers here
                // For example, if they have a specific user_type_id:
                // $query->where('user_type_id', 3); // Assuming 3 is for Account Managers
                
                // Or if you have a role/status field:
                $query->whereNotNull('name'); // Adjust based on your user table structure
            })
            ->orderBy('id')
            ->get();

            if ($accountManagers->isEmpty()) {
                $this->warn('⚠️ No active account managers found.');
                Log::warning('AssignNewLeadsCron: No active account managers found');
                return Command::FAILURE;
            }

            // Get all leads with multiple statuses and no assigned user
            $unassignedLeads = Lead::whereIn('status', ['new', 'follow_up', 'system', 'to_be_expired', 'expired'])
                ->whereNull('user_id')
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

            // Get the last assigned user index from cache for round robin
            $cacheKey = 'am_assignment_last_index';
            $lastIndex = Cache::get($cacheKey, -1);
            $currentIndex = ($lastIndex + 1) % $accountManagers->count();

            $assignedCount = 0;

            DB::transaction(function () use ($unassignedLeads, $accountManagers, &$currentIndex, &$assignedCount, $cacheKey) {
                foreach ($unassignedLeads as $lead) {
                    $assignedUser = $accountManagers[$currentIndex];
                    
                    // Assign the lead to the current AM
                    $lead->update([
                        'user_id' => $assignedUser->id,
                        'updated_at' => now()
                    ]);

                    $this->line("✅ Assigned Lead #{$lead->id} ({$lead->heading}) to {$assignedUser->name}");
                    
                    $assignedCount++;
                    
                    // Move to next AM in round robin
                    $currentIndex = ($currentIndex + 1) % $accountManagers->count();
                }

                // Store the last used index in cache
                Cache::put($cacheKey, ($currentIndex - 1 + $accountManagers->count()) % $accountManagers->count(), now()->addDays(30));
            });

            $this->newLine();
            $this->info("🎯 Successfully assigned {$assignedCount} leads to account managers.");
            
            // Show round robin status
            $this->showAssignmentSummary($accountManagers, $assignedCount);
            
            Log::info("AssignNewLeadsCron: Successfully assigned {$assignedCount} leads", [
                'assigned_count' => $assignedCount,
                'total_ams' => $accountManagers->count(),
                'next_index' => $currentIndex % $accountManagers->count()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during lead assignment: ' . $e->getMessage());
            Log::error('AssignNewLeadsCron failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
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
        
        // Show current assignment counts for each AM
        $this->info("📈 Current Lead Distribution:");
        foreach ($accountManagers as $am) {
            $leadCount = Lead::where('user_id', $am->id)->count();
            $this->line("   • {$am->name}: {$leadCount} leads");
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
        $this->info("📋 Unassigned leads (all statuses): {$unassignedCount}");
    }
}
