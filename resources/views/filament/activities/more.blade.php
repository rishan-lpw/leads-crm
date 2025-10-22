<div class="space-y-4">
    @forelse ($activities as $activity)
        <div class="rounded border p-3">
            <div class="text-sm text-gray-600">
                {{ $activity->created_at?->format('M d, Y H:i') ?? 'No date' }}
                • By: {{ $activity->user->username ?? 'Unknown' }}
            </div>
            <div class="mt-1 font-semibold">
                {{ ucfirst($activity->activity_type ?? 'Activity') }}
            </div>
            <div class="text-sm">Payment Status: {{ $activity->paymentStatus->payment_status ?? 'N/A' }}</div>
            <div class="mt-2 text-sm text-gray-700">{{ $activity->comments ?? 'No comments' }}</div>
        </div>
    @empty
        <div class="text-gray-500">No more activities.</div>
    @endforelse
</div>

