@php
    $widget = $widgetClass ?? $widget ?? null;
    $leadId = $leadId ?? null;
@endphp

@if ($widget && $leadId)
    @livewire($widget, ['leadId' => $leadId])
@elseif ($widget)
    {{-- Fallback if no leadId available --}}
    @livewire($widget)
@endif

