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
    protected $description = 'Assign unassigned leads to users based on user_value and score ranges using round-robin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting lead assignment process (low/medium/high with round-robin)...');

        try {
            // Fetch user groups — users can belong to multiple
            $userGroups = [
                'low'    => $this->getUsersByValue('low'),
                'medium' => $this->getUsersByValue('medium'),
                'high'   => $this->getUsersByValue('high'),
            ];

            foreach ($userGroups as $key => $users) {
                $this->info("👥 {$key} users found: {$users->count()}");
            }

            $totalAssigned = 0;
            $totalAssigned += $this->assignLeadsByCategory('low',    $userGroups['low']);
            $totalAssigned += $this->assignLeadsByCategory('medium', $userGroups['medium']);
            $totalAssigned += $this->assignLeadsByCategory('high',   $userGroups['high']);

            $this->newLine();
            $this->info("🎯 Total assigned: {$totalAssigned} leads.");

            Log::info('AssignNewLeadsCron completed successfully', [
                'total_assigned' => $totalAssigned,
                'low_users' => $userGroups['low']->count(),
                'medium_users' => $userGroups['medium']->count(),
                'high_users' => $userGroups['high']->count(),
            ]);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error('AssignNewLeadsCron failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Get users by user_value category
     */
    private function getUsersByValue(string $value)
    {
        return User::whereJsonContains('user_value', $value)
            ->orderBy('id')
            ->get();
    }

    /**
     * Assign leads by score range to specific user group
     */
    private function assignLeadsByCategory(string $category, $users): int
    {
        if ($users->isEmpty()) {
            $this->warn("⚠️ No users found for category '{$category}'.");
            return 0;
        }

        // Score range conditions
        $query = Lead::query()->whereNull('user_id');

        switch ($category) {
            case 'low':
                $query->where('score', '<', 3.0);
                break;
            case 'medium':
                $query->where('score', '>=', 3.0)->where('score', '<=', 7.0);
                break;
            case 'high':
                $query->where('score', '>', 7.0);
                break;
        }

        $leads = $query->orderBy('created_at', 'asc')->get();

        if ($leads->isEmpty()) {
            $this->info("📋 No unassigned {$category} leads found.");
            return 0;
        }

        $this->info("📋 Found {$leads->count()} {$category} leads to assign.");
        $this->info("👥 Eligible users: {$users->count()}");

        // Use cache to remember round-robin index
        $cacheKey = "lead_assign_index_{$category}";
        $lastIndex = Cache::get($cacheKey, -1);
        $currentIndex = ($lastIndex + 1) % $users->count();

        $assignedCount = 0;

        foreach ($leads as $lead) {
            $assignee = $users[$currentIndex];

            $updated = Lead::where('id', $lead->id)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $assignee->id,
                    'updated_at' => now(),
                ]);

            if ($updated === 1) {
                $assignedCount++;
                $this->line("✅ Assigned Lead #{$lead->id} (score: {$lead->score}) → {$assignee->name} ({$category})");
                $currentIndex = ($currentIndex + 1) % $users->count();
            } else {
                $this->line("⏭️ Skipped Lead #{$lead->id} (already assigned)");
            }
        }

        // Save last index for next round
        if ($assignedCount > 0) {
            Cache::put($cacheKey, ($currentIndex - 1 + $users->count()) % $users->count(), now()->addDays(30));
        }

        $this->info("🎯 Assigned {$assignedCount} {$category} leads.");
        $this->newLine();

        return $assignedCount;
    }

    /**
     * Optional helpers (not used in handle, kept for convenience)
     */
    public function resetRoundRobin()
    {
        foreach (['low', 'medium', 'high'] as $cat) {
            Cache::forget("am_assignment_last_index_{$cat}");
        }
        $this->info('🔄 Round-robin counters reset for all categories.');
    }

    /**
     * Show round robin status.
     */
    public function showStatus()
    {
        foreach (['low', 'medium', 'high'] as $cat) {
            $users = $this->getUsersByValue($cat);
            $cacheKey = "am_assignment_last_index_{$cat}";
            $lastIndex = Cache::get($cacheKey, -1);
            $nextIndex = ($lastIndex + 1) % max($users->count(), 1);
            $nextUser = $users->get($nextIndex);

            $this->info("{$cat}: users={$users->count()}, last={$lastIndex}, next={$nextIndex}" .
                ($nextUser ? " ({$nextUser->name})" : '')
            );
        }
    }
}
