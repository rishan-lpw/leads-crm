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

