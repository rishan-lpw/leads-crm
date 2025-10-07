<div class="space-y-4" x-data="{ expanded: {} }">
    @if($error)
        <div class="fi-section rounded-xl bg-danger-50 p-4 text-sm text-danger-800 dark:bg-danger-500/10 dark:text-danger-400">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-danger-600 dark:text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <span class="font-semibold">Error loading call logs:</span>
                    <span class="ml-1">{{ $error }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(empty($callLogs))
        <div class="fi-section rounded-xl bg-gray-50 p-8 text-center dark:bg-gray-800/50">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">No call logs available</p>
        </div>
    @else
        @foreach($callLogs as $index => $log)
            <div class="fi-section rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <!-- Header -->
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-500/10">
                            <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">Call Conversation #{{ $index + 1 }}</h4>
                            <div class="mt-1 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $log['date_time'] ?? $log['date'] ?? 'N/A' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $log['duration'] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sentiment Badge -->
                    @if(isset($log['sentiment']))
                        @php
                            $sentiment = strtolower($log['sentiment']);
                            $sentimentColors = [
                                'positive' => 'fi-badge-color-success',
                                'negative' => 'fi-badge-color-danger',
                                'neutral' => 'fi-badge-color-gray',
                                'mixed' => 'fi-badge-color-warning',
                            ];
                            $colorClass = $sentimentColors[$sentiment] ?? 'fi-badge-color-primary';
                        @endphp
                        <span class="fi-badge inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $colorClass }}">
                            {{ ucfirst($log['sentiment']) }}
                        </span>
                    @endif
                </div>

                <!-- Recording -->
                @if(isset($log['recording_url']) && $log['recording_url'])
                    <div class="mb-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-900/50">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Recording:</span>
                            <audio controls class="fi-audio h-8 max-w-md">
                                <source src="{{ $log['recording_url'] }}" type="audio/mpeg">
                                <source src="{{ $log['recording_url'] }}" type="audio/wav">
                                <source src="{{ $log['recording_url'] }}" type="audio/ogg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    </div>
                @endif

                <!-- Summary -->
                @if(isset($log['summary']) || isset($log['summery']))
                    @php
                        $summary = $log['summary'] ?? $log['summery'] ?? '';
                        $summaryId = 'summary-' . $index;
                    @endphp
                    <div class="mt-3">
                        <h5 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Summary:</h5>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <div x-show="!expanded['{{ $summaryId }}']" class="line-clamp-2">
                                {{ $summary }}
                            </div>
                            <div x-show="expanded['{{ $summaryId }}']" x-cloak>
                                {{ $summary }}
                            </div>
                            @if(strlen($summary) > 150)
                                <button 
                                    type="button"
                                    @click="expanded['{{ $summaryId }}'] = !expanded['{{ $summaryId }}']; $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                                    class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 focus:outline-none dark:text-primary-400 dark:hover:text-primary-300"
                                >
                                    <span x-text="expanded['{{ $summaryId }}'] ? 'Read Less' : 'Read More'">Read More</span>
                                    <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': expanded['{{ $summaryId }}'] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Additional Call Details -->
                @if(isset($log['call_type']) || isset($log['status']) || isset($log['caller']) || isset($log['receiver']))
                    <div class="mt-3 grid grid-cols-2 gap-3 border-t border-gray-200 pt-3 text-sm dark:border-gray-700">
                        @if(isset($log['call_type']))
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Type:</span>
                                <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $log['call_type'] }}</span>
                            </div>
                        @endif
                        @if(isset($log['status']))
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Status:</span>
                                <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $log['status'] }}</span>
                            </div>
                        @endif
                        @if(isset($log['caller']))
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Caller:</span>
                                <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $log['caller'] }}</span>
                            </div>
                        @endif
                        @if(isset($log['receiver']))
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Receiver:</span>
                                <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $log['receiver'] }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>

