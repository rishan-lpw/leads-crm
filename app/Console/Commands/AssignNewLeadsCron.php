<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\Cron;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

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

            $totalReassigned = $this->reassignPendingPaymentLeads();
            $totalFollowUpReassigned = $this->reassignFollowUpLeads($userGroups);
            $totalNewTransferReassigned = $this->reassignNewAndTransferLeads($userGroups);
            
            $this->newLine();
            $this->info("🎯 Total assigned: {$totalAssigned} leads.");
            $this->info("🔁 Pending Payment reassignments: {$totalReassigned}");
            $this->info("🔁 Follow-up reassignments: {$totalFollowUpReassigned}");
            $this->info("🔁 New/Transfer reassignments: {$totalNewTransferReassigned}");

            Log::info('AssignNewLeadsCron completed successfully', [
                'total_assigned' => $totalAssigned,
                'total_reassigned_pending_payment' => $totalReassigned,
                'total_follow_up_reassigned' => $totalFollowUpReassigned,
                'total_new_transfer_reassigned' => $totalNewTransferReassigned,
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
    private function assignLeadsByCategory(string $category, $users, array $options = []): int
    {
        if ($users->isEmpty()) {
            $this->warn("⚠️ No users found for category '{$category}'.");
            return 0;
        }

        $onlyUnassigned = $options['only_unassigned'] ?? true;
        $cacheSuffix = $options['cache_suffix'] ?? ($onlyUnassigned ? 'assign' : 'reassign');
        $orderBy = $options['order_by'] ?? ($onlyUnassigned ? 'created_at' : 'updated_at');
        $orderDirection = $options['order_direction'] ?? 'asc';
        $skipScore = $options['skip_score'] ?? false;
        $context = $options['context'] ?? $category;
        $actionVerb = $options['action'] ?? ($onlyUnassigned ? 'Assigned' : 'Reassigned');
        $lineEmoji = $options['line_emoji'] ?? ($onlyUnassigned ? '✅' : '🔁');
        $emptyMessage = $options['empty_message'] ?? (
            $onlyUnassigned
                ? "📋 No unassigned {$context} leads found."
                : "📋 No {$context} leads eligible for reassignment."
        );

        $baseQuery = $options['query'] ?? Lead::query();
        $query = clone $baseQuery;

        if ($onlyUnassigned) {
            $query->whereNull('user_id');
        } else {
            $query->whereNotNull('user_id');
        }

        if (! $skipScore) {
            $query = $this->applyScoreRange($query, $category);
        }

        $leads = $query->orderBy($orderBy, $orderDirection)->get();

        if ($leads->isEmpty()) {
            $this->info($emptyMessage);
            return 0;
        }

        $this->info("📋 Found {$leads->count()} {$context} leads to process.");
        $this->info("👥 Eligible users: {$users->count()}");

        // Use cache to remember round-robin index
        $cacheKey = "lead_assign_index_{$cacheSuffix}_{$category}";
        $lastIndex = Cache::get($cacheKey, -1);
        $currentIndex = ($lastIndex + 1) % $users->count();

        $assignedCount = 0;

        foreach ($leads as $lead) {
            $assignee = $users[$currentIndex];

            if (! $onlyUnassigned && (int) $lead->user_id === (int) $assignee->id) {
                $currentIndex = ($currentIndex + 1) % $users->count();
                continue;
            }

            $updateQuery = Lead::where('id', $lead->id);

            if ($onlyUnassigned) {
                $updateQuery->whereNull('user_id');
            }

            $updated = $updateQuery
                ->update([
                    'user_id' => $assignee->id,
                    'updated_at' => now(),
                ]);

            if ($updated === 1) {
                $assignedCount++;
                $this->line("{$lineEmoji} {$actionVerb} Lead #{$lead->id} → {$assignee->name} ({$context})");
                $currentIndex = ($currentIndex + 1) % $users->count();
            } else {
                $this->line("⏭️ Skipped Lead #{$lead->id} (assignment changed concurrently)");
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

    private function applyScoreRange(Builder $query, string $category): Builder
    {
        switch ($category) {
            case 'low':
                $query->where('score', '<', 3.0);
                break;
            case 'medium':
                $query->whereBetween('score', [3.0, 7.0]);
                break;
            case 'high':
                $query->where('score', '>', 7.0);
                break;
            default:
                $this->warn("⚠️ Unsupported score category '{$category}'.");
                break;
        }

        return $query;
    }

    private function reassignPendingPaymentLeads(): int
    {
        $cronConfig = Cron::where('category', 'Pending Payment')->first();

        if (! $cronConfig || empty($cronConfig->member)) {
            $this->warn('⚠️ No cron configuration found for Pending Payment category or no members defined.');
            return 0;
        }

        $ruleDays = (int) ($cronConfig->rule_1_days ?? 7);
        $eligibleUsers = User::whereIn('id', $cronConfig->member)
            ->orderBy('id')
            ->get();

        if ($eligibleUsers->isEmpty()) {
            $this->warn('⚠️ No users found for Pending Payment cron configuration.');
            return 0;
        }

        $cutoffDate = now()->subDays($ruleDays);
        $query = Lead::query()
            ->where('source', 'Pending Payment')
            ->whereNotNull('user_id')
            ->whereDate('updated_at', '<=', $cutoffDate);

        return $this->assignLeadsByCategory('pending_payment', $eligibleUsers, [
            'query' => $query,
            'cache_suffix' => 'pending_payment',
            'only_unassigned' => false,
            'order_by' => 'updated_at',
            'action' => 'Reassigned',
            'line_emoji' => '🔁',
            'context' => 'Pending Payment',
            'empty_message' => '📋 No Pending Payment leads eligible for reassignment.',
            'skip_score' => true,
        ]);
    }

    private function reassignFollowUpLeads(array $userGroups): int
    {
        $cronConfig = Cron::where('category', 'Other')->first();

        if (! $cronConfig) {
            $this->warn('⚠️ No cron configuration found for Other category.');
            return 0;
        }

        $ruleDays = (int) ($cronConfig->rule_2_days ?? 14);
        $cutoffDate = now()->subDays($ruleDays);

        $baseQuery = Lead::query()
            ->where('status', 'follow_up')
            ->whereNotNull('user_id')
            ->where(function (Builder $query) {
                $query->whereNull('source')
                    ->orWhere('source', '!=', 'Pending Payment');
            })
            ->whereDate('updated_at', '<=', $cutoffDate);

        $total = 0;

        foreach (['low', 'medium', 'high'] as $category) {
            $users = $userGroups[$category] ?? collect();

            $total += $this->assignLeadsByCategory($category, $users, [
                'query' => $baseQuery,
                'cache_suffix' => 'follow_up',
                'only_unassigned' => false,
                'order_by' => 'updated_at',
                'action' => 'Reassigned',
                'line_emoji' => '🔁',
                'context' => "{$category} follow_up",
                'empty_message' => "📋 No {$category} follow_up leads eligible for reassignment.",
            ]);
        }

        return $total;
    }

    private function reassignNewAndTransferLeads(array $userGroups): int
    {
        $cutoffDate = now()->subDays(3);

        $baseQuery = Lead::query()
            ->whereIn('status', ['new', 'transferred'])
            ->whereNotNull('user_id')
            ->where(function (Builder $query) {
                $query->whereNull('source')
                    ->orWhere('source', '!=', 'Pending Payment');
            })
            ->whereDate('updated_at', '<=', $cutoffDate);

        $total = 0;

        foreach (['low', 'medium', 'high'] as $category) {
            $users = $userGroups[$category] ?? collect();

            $total += $this->assignLeadsByCategory($category, $users, [
                'query' => $baseQuery,
                'cache_suffix' => 'new_transfer',
                'only_unassigned' => false,
                'order_by' => 'updated_at',
                'action' => 'Reassigned',
                'line_emoji' => '🔁',
                'context' => "{$category} new/transfer",
                'empty_message' => "📋 No {$category} new/transfer leads eligible for reassignment.",
            ]);
        }

        return $total;
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
