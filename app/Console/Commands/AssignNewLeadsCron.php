<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\User;
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
    protected $description = 'Assign ALL unassigned leads to users with user_type = 1 using round-robin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting assignment of ALL unassigned leads to user_type = 1 (round-robin)…');

        try {
            $accountManagers = $this->getAccountManagers();

            if ($accountManagers->isEmpty()) {
                $this->warn('⚠️ No users found with user_type = 1.');
                Log::warning('AssignNewLeadsCron: No users with user_type = 1 found');
                return Command::FAILURE;
            }

            $pendingCount = Lead::whereNull('user_id')->count();

            if ($pendingCount === 0) {
                $this->info('✅ No unassigned leads found.');
                Log::info('AssignNewLeadsCron: No unassigned leads found');
                return Command::SUCCESS;
            }

            $this->info("📋 Found {$pendingCount} unassigned leads.");
            $this->info("👥 Eligible Assignees (user_type=1): {$accountManagers->count()}");
            $this->newLine();

            // Persist last index across runs for fairness
            $cacheKey = 'am_assignment_last_index_user_type_1';
            $lastIndex = Cache::get($cacheKey, -1);
            $currentIndex = ($lastIndex + 1) % $accountManagers->count();

            $assignedCount = 0;

            // Stream to keep memory low; atomically claim each lead
            foreach (
                Lead::whereNull('user_id')
                    ->orderBy('created_at', 'asc')
                    ->cursor() as $lead
            ) {
                $assignee = $accountManagers[$currentIndex];

                // Atomic claim to avoid race conditions
                $updated = Lead::where('id', $lead->id)
                    ->whereNull('user_id')
                    ->update([
                        'user_id'    => $assignee->id,
                        'updated_at' => now(),
                    ]);

                if ($updated === 1) {
                    $this->line("✅ Assigned Lead #{$lead->id} → {$assignee->name} (user_id: {$assignee->id})");
                    $assignedCount++;
                    // Advance only on success
                    $currentIndex = ($currentIndex + 1) % $accountManagers->count();
                } else {
                    $this->line("⏭️ Skipped Lead #{$lead->id} (already assigned)");
                }
            }

            // Keep fairness across runs
            if ($assignedCount > 0) {
                Cache::put(
                    $cacheKey,
                    ($currentIndex - 1 + $accountManagers->count()) % $accountManagers->count(),
                    now()->addDays(30)
                );
            }

            $this->newLine();
            $this->info("🎯 Successfully assigned {$assignedCount} leads.");

            Log::info('AssignNewLeadsCron: Assignment run complete', [
                'assigned_count' => $assignedCount,
                'total_assignees' => $accountManagers->count(),
                'next_index' => ($assignedCount > 0)
                    ? $currentIndex % $accountManagers->count()
                    : $lastIndex,
            ]);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Error during lead assignment: ' . $e->getMessage());
            Log::error('AssignNewLeadsCron failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Users eligible for assignment: user_type = 1
     */
    private function getAccountManagers()
    {
        return User::where('user_type', 1)
            ->orderBy('id')
            ->get();
    }

    /**
     * Optional helpers (not used in handle, kept for convenience)
     */
    public function resetRoundRobin()
    {
        Cache::forget('am_assignment_last_index_user_type_1');
        $this->info('🔄 Round-robin counter reset for user_type=1.');
    }

    public function showStatus()
    {
        $users = $this->getAccountManagers();

        if ($users->isEmpty()) {
            $this->warn('No users with user_type = 1.');
            return;
        }

        $this->info("👥 Users (user_type=1): {$users->count()}");
        foreach ($users as $ix => $u) {
            $count = Lead::where('user_id', $u->id)->count();
            $this->line("   {$ix}: {$u->name} (id: {$u->id}) — {$count} leads");
        }

        $lastIndex = Cache::get('am_assignment_last_index_user_type_1', -1);
        $nextIndex = ($lastIndex + 1) % $users->count();
        $nextUser = $users[$nextIndex];

        $this->info("🔄 Round-robin status:");
        $this->info("   Last Index: {$lastIndex}");
        $this->info("   Next: {$nextIndex} - {$nextUser->name} (id: {$nextUser->id})");

        $unassigned = Lead::whereNull('user_id')->count();
        $this->info("📋 Unassigned leads: {$unassigned}");
    }
}
