<div class="flex flex-col space-y-1">
  <!-- Contacted Progress (7 stages) -->
  <div class="flex items-center space-x-1">
    @php
      $contactedStages = [];
      if ($record && $record->activities) {
        $activities = $record->activities->sortBy('created_at');
        foreach ($activities as $activity) {
          $category = strtolower($activity->funnel?->category ?? '');
          if ($category === 'contacted') {
            $stage = (int) ($activity->funnel?->stage ?? 0);
            if ($stage >= 1 && $stage <= 7) {
              $contactedStages[$stage] = true;
            }
          }
        }
      }
    @endphp
    
    @for ($i = 1; $i <= 7; $i++)
      @if(isset($contactedStages[$i]))
        <i class="bi bi-{{ $i }}-circle-fill text-green-500" title="Contacted - Stage {{ $i }}"></i>
      @else
        <i class="bi bi-{{ $i }}-circle text-gray-300" title="Contacted - Stage {{ $i }}"></i>
      @endif
    @endfor
  </div>

  <!-- RNA Progress (3 stages) -->
  <div class="flex items-center space-x-1">
    @php
      $rnaStages = [];
      if ($record && $record->activities) {
        $activities = $record->activities->sortBy('created_at');
        foreach ($activities as $activity) {
          $category = strtolower($activity->funnel?->category ?? '');
          if ($category === 'rna') {
            $stage = (int) ($activity->funnel?->stage ?? 0);
            if ($stage >= 1 && $stage <= 3) {
              $rnaStages[$stage] = true;
            }
          }
        }
      }
    @endphp
    
    @for ($i = 1; $i <= 3; $i++)
      @if(isset($rnaStages[$i]))
        <i class="bi bi-{{ $i }}-circle-fill text-yellow-500" title="RNA - Stage {{ $i }}"></i>
      @else
        <i class="bi bi-{{ $i }}-circle text-gray-300" title="RNA - Stage {{ $i }}"></i>
      @endif
    @endfor
  </div>

  <!-- Not Interested Progress (2 stages) -->
  <div class="flex items-center space-x-1">
    @php
      $notInterestedStages = [];
      if ($record && $record->activities) {
        $activities = $record->activities->sortBy('created_at');
        foreach ($activities as $activity) {
          $category = strtolower($activity->funnel?->category ?? '');
          if ($category === 'not_interested') {
            $stage = (int) ($activity->funnel?->stage ?? 0);
            if ($stage >= 1 && $stage <= 2) {
              $notInterestedStages[$stage] = true;
            }
          }
        }
      }
    @endphp
    
    @for ($i = 1; $i <= 2; $i++)
      @if(isset($notInterestedStages[$i]))
        <i class="bi bi-{{ $i }}-circle-fill text-red-500" title="Not Interested - Stage {{ $i }}"></i>
      @else
        <i class="bi bi-{{ $i }}-circle text-gray-300" title="Not Interested - Stage {{ $i }}"></i>
      @endif
    @endfor
  </div>
</div>

<!-- Make sure Bootstrap Icons CSS is included in your application -->
