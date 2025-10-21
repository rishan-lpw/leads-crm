@php
    $widgetClass = $widget ?? null;
@endphp

@if ($widgetClass)
    @livewire($widgetClass)
@endif

<div>
    @php
        $widgetClass = $widget ?? null;
        $record = $record ?? null;
        
        if ($widgetClass && $record) {
            $widgetInstance = app($widgetClass);
            $widgetInstance->record = $record;
            echo $widgetInstance->render();
        }
    @endphp
</div>

