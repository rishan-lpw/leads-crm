@props(['record'])

@php
    $activities = $record->activities ?? collect();
    $activitiesSorted = $activities->sortBy('created_at');
    
    if ($activitiesSorted->isEmpty()) {
        $output = '<span class="text-gray-400 text-sm">No Activity</span>';
    } else {
        // Color mapping by category
        $categoryColors = [
            'rna' => [
                'bg' => 'bg-red-500',
                'text' => 'text-white',
                'border' => 'border-red-600',
                'label' => 'RNA',
                'max_stages' => 2
            ],
            'follow_up' => [
                'bg' => 'bg-yellow-500',
                'text' => 'text-white',
                'border' => 'border-yellow-600',
                'label' => 'Follow-Up',
                'max_stages' => 3
            ],
            'contacted' => [
                'bg' => 'bg-green-500',
                'text' => 'text-white',
                'border' => 'border-green-600',
                'label' => 'Contacted',
                'max_stages' => 7
            ],
        ];
        
        $icons = [];
        
        foreach ($activitiesSorted as $activity) {
            $category = strtolower($activity->funnel?->category ?? '');
            $stage = (int) ($activity->funnel?->stage ?? 0);
            
            if ($category && isset($categoryColors[$category]) && $stage > 0) {
                $colorData = $categoryColors[$category];
                
                // Ensure stage doesn't exceed max stages for category
                $stage = min($stage, $colorData['max_stages']);
                
                $icons[] = sprintf(
                    '<span class="inline-flex items-center justify-center w-7 h-7 rounded-full %s %s border-2 %s shadow-sm font-semibold text-sm transition-all hover:scale-110" 
                          title="%s - Stage %d">
                        %d
                    </span>',
                    $colorData['bg'],
                    $colorData['text'],
                    $colorData['border'],
                    $colorData['label'],
                    $stage,
                    $stage
                );
            }
        }
        
        $output = $icons 
            ? '<div class="flex flex-wrap gap-1.5 items-center">' . implode('', $icons) . '</div>'
            : '<span class="text-gray-400 text-sm italic">No funnel data</span>';
    }
@endphp

{!! $output !!}

